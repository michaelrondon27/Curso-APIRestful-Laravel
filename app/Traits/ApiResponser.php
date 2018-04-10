<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

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

		$collection = $this->filterData($collection, $transformer);
		$collection = $this->sortData($collection, $transformer);
		$collection = $this->paginate($collection);
		$collection = $this->transformData($collection, $transformer);
		$collection = $this->cacheResponse($collection);

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

	protected function filterData(Collection $collection, $transformer)
	{
		foreach ( request()->query() as $query ) {
			$attribute = $transformer::originalAttribute($query);

			if ( isset($attribute, $value) ) {
				$collection = $collection->where($attibute, $value);
			}
		}

		return $collection;
	}

	protected function sortData(Collection $collection, $transformer)
	{
		if ( request()->has('sort_by') ) {
			$attibute = $transformer::originalAttribute(request()->sort_by);

			$collection = $collection->sortBy->{$attibute};
		}

		return $collection;
	}

	protected function paginate(Collection $collection)
	{
		$rules = [
			'per_page' => 'int|min:2|max:50'
		];

		Validator::validate(request()->all(), $rules);

		$page = LengthAwarePaginator::resolveCurrentPage();

		$perPage = 15;
		if ( request()->has('per_page') ) {
			$perPage = (int) request()->per_page;
		}

		$results = $collection->slice( ($page -1) * $perPage, $perPage)->values();

		$paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
			'path' => LengthAwarePaginator::resolveCurrentPath(),
		]);

		$paginated->appends(request()->all());

		return $paginated;
	}

	protected function transformData($data, $transformer)
	{
		$transformation = fractal($data, new $transformer);
		return $transformation->toArray();
	}

	protected function cacheResponse($data)
	{
		$url = request()->url();

		return Cache::remember($url, 15/60, function() use ($data) {
			return $data;
		});
	}

}