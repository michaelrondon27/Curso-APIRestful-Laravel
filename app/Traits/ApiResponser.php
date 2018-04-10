<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

trait ApiResponser
{

	private function successRespose($data, $code)
	{
		return response()->json( $data, $code );
	}

	protected function errorResponse($message, $code)
	{
		return response()->json( ['error' => $message, 'code' => $code], $code );
	}

	protected function showAll(Collection $collection, $code = 200)
	{
		if ( $collection->isEmpty() ) {
			return $this->successRespose($collection, $code);
		}

		$transformer = $collection->first()->transformer;
		$collection = $this->transformData($collection, $transformer);

		return $this->successRespose( [$collection], $code );
	}

	protected function showOne(Model $instance, $code = 200)
	{
		$transformer = $instance->transformer;
		$instance = $this->transformData($instance, $transformer);

		return $this->successRespose( [$instance], $code );
	}

	public function showMessage($message, $code = 200)
	{
		return $this->successRespose( ['data' => $message], $code );
	}

	protected function transformData($data, $transformer)
	{
		$transformation = fractal($data, new $transformer);
		return $transformation->toArray();
	}

}