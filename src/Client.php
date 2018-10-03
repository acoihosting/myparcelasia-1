<?php
// A client for MyParcelAsia API
// Based on documentation at https://myparcelasia.com/api/v1

namespace apih\MyParcelAsia;

class Client
{
	const DEMO_URL = 'https://demo.myparcelasia.com/api/v1/';
	const LIVE_URL = 'https://myparcelasia.com/api/v1/';

	protected $api_key;
	protected $secret_key;
	protected $url;
	protected $use_ssl = true;
	protected $last_error;

	public function __construct($api_key, $secret_key)
	{
		$this->api_key = $api_key;
		$this->secret_key = $secret_key;
		$this->url = self::LIVE_URL;
	}

	public function useDemo($flag = true)
	{
		$this->url = $flag ? self::DEMO_URL : self::LIVE_URL;
	}

	public function useSsl($flag = true)
	{
		$this->use_ssl = $flag;
	}

	public function getLastError()
	{
		return $this->last_error;
	}

	protected function logError($function, $request, $response)
	{
		$this->last_error = [
			'function' => $function,
			'request' => $request,
			'response' => $response
		];

		$error_message = 'MyParcelAsia Error:' . PHP_EOL;
		$error_message .= 'function: ' . $function . PHP_EOL;
		$error_message .= 'request: ' . PHP_EOL;
		$error_message .= '-> url: ' . $request['url'] . PHP_EOL;
		$error_message .= '-> data: ' . json_encode($request['data']) . PHP_EOL;
		$error_message .= 'response: ' . PHP_EOL;
		$error_message .= '-> http_code: ' . $response['http_code'] . PHP_EOL;
		$error_message .= '-> body: ' . $response['body'] . PHP_EOL;

		error_log($error_message);
	}

	protected function curlInit()
	{
		$this->last_error = null;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

		if ($this->use_ssl === false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		return $ch;
	}

	protected function curlPostRequest($function, $action, $data = [])
	{
		$url = $this->url . $action;

		$data = array_merge([
			'api_key' => $this->api_key
		], $data);

		$ch = $this->curlInit();

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_URL, $url);

		$body = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$decoded_body = json_decode($body, true);

		if ($http_code !== 200 || json_last_error() !== JSON_ERROR_NONE) {
			$this->logError(
				$function,
				compact('url', 'data'),
				compact('http_code', 'body')
			);

			return null;
		}

		return $decoded_body;
	}

	public function getProductTypes()
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_product_types');
	}

	public function getCountries()
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_countries');
	}

	public function getMyStates()
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_my_states');
	}

	public function getQuotes($data)
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_quotes', $data);
	}

	public function createShipment($data)
	{
		return $this->curlPostRequest(__FUNCTION__, 'create_shipment', $data);
	}

	public function getCartContent()
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_cart_content');
	}

	public function checkout($shipment_id)
	{
		if (!is_array($shipment_id)) {
			$shipment_id = [$shipment_id];
		}

		$data = [
			'shipment_id' => $shipment_id
		];

		return $this->curlPostRequest(__FUNCTION__, 'checkout', $data);
	}

	public function getAllShipments()
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_all_shipment');
	}

	public function getAllConsignmentNotes()
	{
		return $this->curlPostRequest(__FUNCTION__, 'get_all_connote');
	}
}
?>