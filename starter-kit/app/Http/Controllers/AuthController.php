<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Session;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use Validator;


use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/*❗ - This is auth controller for vTiger instance */

class AuthController extends Controller
{
  protected $url;


  public function __construct()
  {
    $this->url = config('vtiger.vtiger_api_endpoint');
  }

  protected function api($params)
  {

    if ($this->url) {
      $response = Http::asForm()
        ->withBasicAuth($params['username'], $params['password'])
        ->accept('*/*')
        ->asForm()
        ->post($this->url, $params);

      /*TODO - Error handling try ... catch */
      // Log::info('$response->getBody():' . $response->getBody());
      return $response->getBody();
    }
    abort(500); /*NOTE - throw exception   */
  }

  public function login(Request $request)
  {
    $request->validate([
      'username' => 'required|string|email',
      'password' => 'required|string'
    ]);

    $args = array(
      '_operation' => 'Ping',
      'username' => request('username'),
      'password' => request('password'),
    );
    $response = json_decode(self::api($args), true);
    if (isset($response['result']) &&  $response['result'] == 'login success') {
      //*TODO - add mySession handling  */

      self::updateLoginDetails('Login');
      //Get user data
      $userProfile = self::fetchProfile();
      //$userModules = self::fetchModules();

      return response()->json([
        [
          'userAbilityRules' => 'Bearer',
          'accessToken' => '1111',
          'userData' => $userProfile,
          //  'userModules' => $userModules,
        ],
        200,
      ]);
    } else {
      return response()->json([
        'errors' => ['email' => 'Invalid email or password'],
      ], 401);
    }
  }

  protected function updateLoginDetails($status)
  {
    $args = array(
      '_operation' => 'UpdateLoginDetails',
      'status' => $status,
      'username' => request('username'),
      'password' => request('password'),
    );

    return self::api($args);
  }


  public function logout(Request $request)
  {
    $request->validate([
      'username' => 'required|string|email',
      'password' => 'required|string'
    ]);

    $response = json_decode(self::updateLoginDetails('Logout'), true);

    /*TODO - Session handling was here */

    if (isset($response['result']) &&  $response['result'] == 'true') {
      return response()->json([
        [
          'success' => 'true',
        ],
        200,
      ]);
    } else {
      return response()->json([
        'errors' => ['email' => 'Logout failed!'],
      ], 401);
    }
  }

  public function fetchProfile()
  {
    $params = array(
      '_operation' => 'FetchProfile',
      'username' => request('username'),
      'password' => request('password'),
    );

    $userProfile = json_decode(self::api($params), true);

    if (isset($userProfile['success']) &&  $userProfile['success'] == 'true') {
      $userProfile = json_encode($userProfile['result']['customer_details']);
      //match fields between vTiger and Vuexy
      //Log::info('$userProfile:' . $userProfile);
      $find = ['firstname', 'label'];
      $replacement = ['name', 'fullName'];

      $result = str_replace($find, $replacement, $userProfile);
      //$result['Role'] = 'User';

      Log::info('$newarray:' . $result);

      return json_decode($result, true);
    }
  }

  public function fetchModules()
  {
    $params = array(
      '_operation' => 'FetchModules',
      'language' => 'de_DE',
      'username' => request('username'),
      'password' => request('password'),
    );

    $userModules = json_decode(self::api($params), true);

    if (isset($userModules['success']) &&  $userModules['success'] == 'true') {


      return response()->json(
        [
          'data' => $userModules['result']['modules']['types'],
          'information' => $userModules['result']['modules']['information']
        ],
        200,
      );
    } else {
      return response()->json([
        'errors' => 'Fetch modules failed!',
      ], 401);
    }
  }
  public function addCard()
  {
    $params = array(
      '_operation' => 'SaveRecord',
      'module' => 'Billing',
      'username' => request('username'),
      'password' => request('password'),
      'values' => request('record'),
      'recordId' => request('recordId')

    );

    $addCardResponse = json_decode(self::api($params), true);

    if (isset($addCardResponse['success']) &&  $addCardResponse['success'] == 'true') {
      return response()->json(
        [
          'id' => $addCardResponse['result']['record']['id']
        ],
        200,
      );
    } else {
      return response()->json([
        'errors' => 'Add card failed!',
      ], 401);
    }
  }

  public function deleteCard()
  {
    $params = array(
      '_operation' => 'DeleteRecord',
      'module' => 'Billing',
      'username' => request('username'),
      'password' => request('password'),
      'recordId' => request('id')
    );

    $deleteCardResponse = json_decode(self::api($params), true);

    if (isset($deleteCardResponse['success']) &&  $deleteCardResponse['success'] == 'true') {
      return response()->json(
        [
          //FIXME - handele return value'id' => $deleteCardResponse['result']['record']['id']
        ],
        200,
      );
    } else {
      return response()->json([
        'errors' => 'Delete card failed!',
      ], 401);
    }
  }


  public function fetchInvoices(Request $request)
  {
    $params = array(
      '_operation' => 'FetchRecords',
      'module' => 'Invoice',
      'moduleLabel' => 'Invoice',
      'username' => $request->username,
      'password' => $request->password,
      'mode' => 'all',
      'status' => $request->status,
      'selectedRange' => $request->selectedRange,
      'pageLimit' =>  $request->pageLimit,
      'orderBy' => $request->orderBy,
      'page' => $request->page - 1,
      'order' => $request->order,
      'fields' => ($request->status == null ? '' : json_encode(['invoicestatus' => $request->status,])),
    );

    Log::info('$params:' . json_encode($params));

    $fetchInvoicesResponse = json_decode(self::api($params), true);

    if (isset($fetchInvoicesResponse['success']) &&  $fetchInvoicesResponse['success'] == 'true') {
      $toalInvoices = $fetchInvoicesResponse['result']['count'];
      $invoicesArray = $fetchInvoicesResponse['result'];
      unset($invoicesArray['count']);
      return response()->json(
        [
          'invoices' => $invoicesArray,
          'totalInvoices' => $toalInvoices
        ],
        200,
      );
    } else {
      return response()->json([
        'errors' => 'Fetch invoices failed!',
      ], 401);
    }
  }

  public function fetchCreditCards(Request $request)
  {
    $params = array(
      '_operation' => 'FetchRecords',
      'module' => 'Billing',
      'moduleLabel' => 'Billing',
      'username' => $request->username,
      'password' => $request->password,
      'q' => ['mode' => 'mine'],
    );

    $userCardsResponse = json_decode(self::api($params), true);
    Log::info('$params:' . json_encode($userCardsResponse));

    if (isset($userCardsResponse['success']) &&  $userCardsResponse['success'] == 'true') {

      $userCardsArray = $userCardsResponse['result'];
      unset($userCardsArray['count']);

      foreach ($userCardsArray as $key => &$value) {
        $userCardsArray[$key]['value'] = substr($userCardsArray[$key]['id'], 3, null);
      }



      return response()->json(
        [
          'cards' => $userCardsArray,
        ],
        200,
      );
    } else {
      return response()->json(
        [
          'errors' => ['email' => 'Cards fetching failed!'],
        ],
        401
      );
    }
  }


  public function fetchProducts(Request $request)
  {
    $params = array(
      '_operation' => 'FetchRecords',
      'module' => 'Products',
      'moduleLabel' => 'products',
      'username' => $request->username,
      'password' => $request->password,
      'q' => ['mode' => 'all'],
    );

    $productsResponse = json_decode(self::api($params), true);
    Log::info('$params:' . json_encode($productsResponse));

    if (isset($productsResponse['success']) &&  $productsResponse['success'] == 'true') {

      $productsArray = $productsResponse['result'];
      unset($productsArray['count']);

      foreach ($productsArray as $key => &$value) {
        $paramsMeta = array(
          '_operation' => 'GetMeta',
          'module' => 'Products',
          'moduleLabel' => 'Products',
          'username' => $request->username,
          'password' => $request->password,
          'recordId' => $value['id'],
        );

        $getMetaResponse = json_decode(self::api($paramsMeta), true);

        if (isset($getMetaResponse['success']) &&  $getMetaResponse['success'] == 'true') {

          $value['url'] = config('vtiger.vtiger_public_api_endpoint') . $getMetaResponse['result']['image_details']['url'];
          $value['description'] = nl2br($value['description']);
          Log::info('$key:' . json_encode($key) . ' $value:' . json_encode($value));
        }
      }

      Log::info('$productsArray' . json_encode($productsArray));
      return response()->json(
        [
          'products' => $productsArray,
        ],
        200,
      );
    } else {
      return response()->json(
        [
          'errors' => ['email' => 'Products fetching failed!'],
        ],
        401
      );
    }
  }

  public function fetchServices(Request $request)
  {
    $params = array(
      '_operation' => 'FetchRecords',
      'module' => 'Services',
      'label' => 'Services',
      'username' => $request->username,
      'password' => $request->password,
      'q' => ['mode' => 'all'],
      'orderBy' => 'cf_sub_id',
      'order' => 'ASC',
    );

    $servicesResponse = json_decode(self::api($params), true);


    if (isset($servicesResponse['success']) &&  $servicesResponse['success'] == 'true') {
      Log::info('$servicesResponse: ' . json_encode($servicesResponse)); //FIXME - Remove all these logs

      $userServicessArray = $servicesResponse['result'];
      foreach ($userServicessArray as $key => &$value) {
        //Log::info('$value[description]: ' . json_encode($value['description'])); //FIXME - Remove all these logs
        if (isset($value['description'])) {
          $featuresArray = preg_split("/\n/", $value['description']);
          $userServicessArray[$key]['features'] = $featuresArray;
        }
      }
      unset($userServicessArray['count']);
      Log::info('$userServicessArray: ' . json_encode($userServicessArray)); //FIXME - Remove all these logs

      return response()->json(
        [
          'services' => $userServicessArray,
        ],
        200,
      );
    } else {
      return response()->json(
        [
          'errors' => ['email' => 'Services fetching failed!'],
        ],
        401
      );
    }
  }


  public function getStripeKey(Request $request)
  {
    return response()->json(
      config('vtiger.STRIPE_PUBLISHABLE_KEY'),
      200,
    );
  }

  public function createPaymentIntent(Request $request)
  {

    $stripe = new \Stripe\StripeClient(config('vtiger.STRIPE_SECRET_KEY'));
    $intent = $stripe->paymentIntents->create(
      [
        'amount' => '100',
        'currency' => 'usd',
        // In the latest version of the API, specifying the `automatic_payment_methods` parameter is optional because Stripe enables its functionality by default.
        'automatic_payment_methods' => ['enabled' => true],
      ]
    );

    Log::info('$intent->client_secret: ' . json_encode($intent->client_secret)); //FIXME - Remove all these logs

    return response()->json(
      $intent->client_secret,
      200,
    );
  }

  public function createSetupIntent(Request $request)
  {

    $stripe = new \Stripe\StripeClient(config('vtiger.STRIPE_SECRET_KEY'));
    $intent = $stripe->setupIntents->create([
      'customer' => $request->stripeCustomerId, //FIXME - add user info
      'automatic_payment_methods' => ['enabled' => true],
    ]);


    Log::info('$intent->client_secret: ' . json_encode($intent->client_secret)); //FIXME - Remove all these logs

    return response()->json(
      $intent->client_secret,
      200,
    );
  }

  public function addStripeCard()
  {
    $stripe = new \Stripe\StripeClient(config('vtiger.STRIPE_SECRET_KEY'));
    Log::info('$pm: ' . json_encode(request('stripePaymentMethodId'))); //FIXME - Remove all these logs

    $paymentMethod = $stripe->customers->retrievePaymentMethod(request('stripeCustomerId'), request('stripePaymentMethodId'), []);
    if (!isset($paymentMethod['id'])) {
      return response()->json([
        'errors' => 'Add card failed!',
      ], 401);
    }
    $values = array(
      'billingname' => '**** **** **** ' . $paymentMethod['card']['last4'],
      'payment' => 'Credit or debit card',
      'cardnumber' => '**** **** **** ' . $paymentMethod['card']['last4'],
      'cardexpirationdate' => $paymentMethod['card']['exp_month'] . '/' . $paymentMethod['card']['exp_year'],
      'cardcvc' => '***',
      'cardholdername' => request('stripePaymentMethodId'),
      'type' => $paymentMethod['card']['brand'],
    );

    Log::info('$values: ' . json_encode($values)); //FIXME - Remove all these logs

    $params = array(
      '_operation' => 'SaveRecord',
      'module' => 'Billing',
      'username' => request('username'),
      'password' => request('password'),
      'values' => $values,
      'recordId' => request('recordId')

    );

    $addCardResponse = json_decode(self::api($params), true);

    if (isset($addCardResponse['success']) &&  $addCardResponse['success'] == 'true') {
      return response()->json(
        [
          'id' => $addCardResponse['result']['record']['id']
        ],
        200,
      );
    } else {
      return response()->json([
        'errors' => 'Add card failed!',
      ], 401);
    }
  }

  public function deleteStripeCard()
  {

    $stripe = new \Stripe\StripeClient(config('vtiger.STRIPE_SECRET_KEY'));
    $paymentMethod = $stripe->paymentMethods->detach(request('stripePaymentMethodId'), []);
    Log::info('$pm: ' . json_encode(request('stripePaymentMethodId'))); //FIXME - Remove all these logs

    $params = array(
      '_operation' => 'DeleteRecord',
      'module' => 'Billing',
      'username' => request('username'),
      'password' => request('password'),
      'recordId' => request('id')
    );

    $deleteCardResponse = json_decode(self::api($params), true);

    if (isset($deleteCardResponse['success']) &&  $deleteCardResponse['success'] == 'true') {
      return response()->json(
        [
          //FIXME - handele return value'id' => $deleteCardResponse['result']['record']['id']
        ],
        200,
      );
    } else {
      return response()->json([
        'errors' => 'Delete card failed!',
      ], 401);
    }
  }





  public function fetchCurrentPlan(Request $request)
  {
    $params = array(
      '_operation' => 'FetchRecords',
      'module' => 'Subscriptions',
      'moduleLabel' => 'Subscriptions',
      'username' => $request->username,
      'password' => $request->password,
      'q' => ['mode' => 'mine'],
    );

    $subscriptionsResponse = json_decode(self::api($params), true);


    if (isset($subscriptionsResponse['success']) &&  $subscriptionsResponse['success'] == 'true') {
      Log::info('$subscriptionsResponse: ' . json_encode($subscriptionsResponse)); //FIXME - Remove all these logs
      unset($subscriptionsResponse['result']['count']);

      $params = array(
        '_operation' => 'FetchRecords',
        'module' => 'Services',
        'label' => 'Services',
        'username' => $request->username,
        'password' => $request->password,
        'q' => ['mode' => 'all'],
        'orderBy' => 'cf_sub_id',
        'order' => 'ASC',
      );
      $servicesResponse = json_decode(self::api($params), true);
      if (isset($servicesResponse['success']) &&  $servicesResponse['success'] == 'true') {

        $userServicessArray = $servicesResponse['result'];
        foreach ($userServicessArray as $key => &$value) {
          //Log::info('$value[description]: ' . json_encode($value['description'])); //FIXME - Remove all these logs
          if (isset($value['description'])) {
            $featuresArray = preg_split("/\n/", $value['description']);
            $userServicessArray[$key]['features'] = $featuresArray;
          }
        }
        unset($userServicessArray['count']);


        return response()->json(
          [
            'plan' =>
            [
              'gatewayName' => $subscriptionsResponse['result']['0']['subscriptiongateway']['label'],
              'planName' => $subscriptionsResponse['result']['0']['subscriptionplan']['label'],
              'startDate' => $subscriptionsResponse['result']['0']['subscriptionstart'],
              'payment' => $subscriptionsResponse['result']['0']['subscriptionpayment'],
            ],
            'services' => $userServicessArray,
          ],
          200,
        );
      }
    }
    return response()->json(
      [
        'errors' => ['email' => 'Services fetching failed!'],
      ],
      401
    );
  }















  /**
   * Get the authenticated User
   *
   * @return [json] user object
   */
  public function user(Request $request)
  {
    return response()->json($request->user());
  }
}
