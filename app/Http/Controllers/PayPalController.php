<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payments;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
/** All Paypal Details class **/
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Redirect;
use Session;
use URL;class PayPalController extends Controller
{
    private $_api_context;
    public function __construct()
    {
        /** PayPal api context **/
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }
    
    public function payment()
    {
        $data = [];
        $data['items'] = [
            [
                'name' => 'ItSolutionStuff.com',
                'price' => 100,
                'desc'  => 'Description for ItSolutionStuff.com',
                'qty' => 1
            ]
        ];
  
        $data['invoice_id'] = 10;
        $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
        $data['return_url'] = route('payment.success');
        $data['cancel_url'] = route('payment.cancel');
        $data['total'] = 100;
  
        $provider = new ExpressCheckout;
  
        $response = $provider->setExpressCheckout($data);
  
        $response = $provider->setExpressCheckout($data, true);
  
        return response()->json(['status' => true,'code' => 200,'data'=>$response]); 
    }
   
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        dd('Your payment is canceled. You can create cancel page here.');
    }
  
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        $response = $provider->getExpressCheckoutDetails($request->token);
  
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            dd('Your payment was successfully. You can create success page here.');
        }
  
        dd('Something is wrong.');
    }
    public function payWithpaypal(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'order_id' => 'required|integer'
        ]);
   
        
        if($validator->fails()){
            return response()->json(['status' => false,'code' => 400,'error'=>$validator->errors()]); 
        }

        //print_r($request->user->id);die();
        if(!$order = Order::where('id',$request->order_id)->where('user_id',$request->user->id)->first())
            return response()->json(['status' => false,'code' => 400,'error'=>'Order Not Found']);


        //print_r($order->price);die();
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $item_1 = new Item();
        $item_1->setName('Coins') /** item name **/
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice(env('COIN_PRICE')); /** unit price **/
        $item_list = new ItemList();
        $item_list->setItems(array($item_1));
        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($order->price);
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Order Number #'.$order->id.'User_id #'.$request->user->id);
        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::to('status?user_id='.$request->user->id.'&order_id='.$order->id)) /** Specify return URL **/
            ->setCancelUrl(URL::to('status'));
        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
        /** dd($payment->create($this->_api_context));exit; **/
        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\Config::get('app.debug')) {
    //            \Session::put('error', 'Connection- timeout');
                return response()->json(['status' => false,'code' => 400,'error'=>'Connection- timeout']);
     //           return Redirect::to('/');
            } else {
//                \Session::put('error', 'Some error occur, sorry for inconvenient');
                
                return response()->json(['status' => false,'code' => 400,'error'=>'Some error occur, sorry for inconvenient']);
 
  //              return Redirect::to('/');
            }
        }
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }
        /** add payment ID to session **/
        //print_r($payment->getId());die();
        //print_r($redirect_url);die();
        //Session::put('paypal_payment_id', $payment->getId());
        if (isset($redirect_url)) {
            /** redirect to paypal **/
            return response()->json(['status' => true,'code' => 200,'url'=>$redirect_url]);
            //return Redirect::away($redirect_url);
        }
        //\Session::put('error', 'Unknown error occurred');
        return response()->json(['status' => false,'code' => 400,'error'=>'Unknown error occurred']);
        //return Redirect::to('/');
    }
    public function getPaymentStatus(Request $request)
    {
        //print_r($request->all());die();
        /** Get the payment ID before session clear **/
        $payment_id = $request->payment_id;
        /** clear the session payment ID **/
        Session::forget('paypal_payment_id');
        if (empty($request->payer_i_d) || empty($request->token)) {
            \Session::put('error', 'Payment failed');
            return response()->json(['status' => false,'code' => 400,'error'=>'Payment failed']);
            return Redirect::to('/');
        }
        $payment = Payment::get($payment_id, $this->_api_context);
        //return response()->json(['status' => true,'code' => 200,'data'=>$payment]);
        $res_pay = json_decode($payment);
        //print_r($res_pay);die();
        $execution = new PaymentExecution();
        $execution->setPayerId($request->payer_i_d);
        /**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);
        if ($result->getState() == 'approved') {
            //\Session::put('success', 'Payment success');
            $pay = new Payments();
            $pay->user_id =  $request->user_id;
            $pay->order_id =  $request->order_id;
            $pay->payment_id =  $res_pay->id;
            $pay->payer_id =  $res_pay->payer->payer_info->payer_id;
            $pay->status =  $res_pay->payer->status;
            $pay->payment_method =  $res_pay->payer->payment_method;
            $pay->country_code =  $res_pay->payer->payer_info->shipping_address->country_code;
            $pay->total_paid =  $res_pay->transactions[0]->amount->total;
            $pay->currency =  $res_pay->transactions[0]->amount->currency;
            $pay->save();

            return Redirect::to('http://localhost:4200/home?status=success');
        }
        //\Session::put('error', 'Payment failed');
        return Redirect::to('http://localhost:4200/home');
    }
    public function index()
    {
        return view('paywithpaypal');
    }
}

