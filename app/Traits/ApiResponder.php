<?php

namespace App\Traits;

trait ApiResponse
{

	private function successRespose( $data, $code )
	{
		return response()->json( $data, $code );
	}

	protected function errorResponse( $message, $code )
	{
		return response()->json( ['error' => $message, 'code' => $code], $code );
	}

	protected function showAll( Colection $collection, $code = 200 )
	{
		return $this->successRespose( ['data' => $collection], $code );
	}

	protected function showOne( Model $instance, $code = 200 )
	{
		return $this->successRespose( ['data' => $instance], $code );
	}

}