<?php

class Kephost
{
	public string $apiKey = ''; // Ide írd be az API kulcsot
	private string $host = 'kephost.net';
	private ?int $gallery_id = 0;
	private string $adult = 'no';

	public function setGalleryId($id)
	{
		$this->gallery_id = intval($id);
		return $this;
	}

	public function setImageTypeToAdult()
	{
		$this->adult = 'yes';
		return $this;
	}

	public function setImageTypeToNonAdult()
	{
		$this->adult = 'no';
		return $this;
	}

	function makeRequest($php_path, $data = [], $method = 'GET', CurlFile $file = null) 
	{
		$url = 'http://' . $this->host . '/' . $php_path . '.php';
		$setopt_array = [];

		if (!empty($data))
		{
			if ($method == 'POST') 
			{
				if ($file)
				{
					$data['file'] = $file;
				}
				$setopt_array[CURLOPT_POST] = 1;
				$setopt_array[CURLOPT_POSTFIELDS] = $data;
			}
			else
			{
				$url .= '?' . http_build_query($data);
			}
		}

		$setopt_array[CURLOPT_URL] = $url;
		$setopt_array[CURLOPT_RETURNTRANSFER] = true;
		$setopt_array[CURLOPT_TIMEOUT] = 15;
		$setopt_array[CURLOPT_MAXREDIRS] = 5;
		$setopt_array[CURLOPT_REFERER] = 'http://' . $this->host . '/index.php';
		$setopt_array[CURLOPT_SSL_VERIFYHOST] = false;
		$setopt_array[CURLOPT_SSL_VERIFYPEER] = false;
		$setopt_array[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';

		$curl = curl_init();
		curl_setopt_array($curl, $setopt_array);
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return $response;
	}

	public function removeImage($uploadedHash, $name)
	{
		return $this->makeRequest('removeImage', ['apiKey' => $this->apiKey, 'uploadedHash' => $uploadedHash, 'image' => $name]);
	}

	public function removeAllImage($uploadedHash)
	{
		return $this->makeRequest('removeAllImage', ['apiKey' => $this->apiKey, 'uploadedHash' => $uploadedHash]);
	}

	public function uploadImage($name, $file, $mime, $uploadedHash = '')
	{
		$fields = [
			'hash' => $this->apiKey,
			'private' => (empty($uploadedHash) ? 'yes' : 'no'),
			'uploadedHash' => $uploadedHash,
			'felnott' => $this->adult ,
			'gallery_id' => $this->gallery_id,
		];

		$curlFile = new CURLFile($file, $mime, $name);
		return $this->makeRequest('upload', $fields, 'POST', $curlFile);
	}
}
