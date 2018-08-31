<?php
// A wrapper for MyParcelAsia API
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

	protected function curlInit()
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($this->use_ssl === false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

		return $ch;
	}

	protected function curlPostRequest($function_name, $action, $query_data = [])
	{
		$query_data = array_merge([
			'api_key' => $this->api_key
		], $query_data);

		$ch = $this->curlInit();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query_data));
		curl_setopt($ch, CURLOPT_URL, $this->url . $action);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$decoded_response = json_decode($response, true);
		$this->last_error = null;

		if ($http_code !== 200 || $decoded_response === null) {
			$this->last_error = [
				'function' => $function_name,
				'http_code' => $http_code,
				'response' => $response
			];

			error_log('MyParcelAsia Error: ' . implode($this->last_error, ' - '));
		}

		return $decoded_response;
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