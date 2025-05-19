<?php

namespace App\Services\Parameter;

use App\Filters\Parameter\FilterParameter;
use App\Models\Parameter\Parameter;
use App\Models\Parameter\ParameterValue;
use Spatie\QueryBuilder\QueryBuilder;

class ParameterService{

    public function allParameters($parameterOrder){

        $parameters = QueryBuilder::for(ParameterValue::class)
        ->allowedFilters([
            //AllowedFilter::custom('search', new FilterParameter()), // Add a custom search filter
            //AllowedFilter::exact('parameterOrder', 'parameter_id'),
        ])
        ->parameterOrder($parameterOrder)
        ->get();
        return $parameters;

    }

    public function createParameter(array $parameterData){

        $parameter = Parameter::where('parameter_order', $parameterData['parameterOrder'])->first();

        $paramteterValue = ParameterValue::create([
            'parameter_id' => $parameter->id,
            'parameter_order' => $parameterData['parameterOrder'],
            'parameter_value' => $parameterData['parameterValue'],
            'description' => $parameterData['description'],
        ]);

        return $paramteterValue;

    }

    public function editParameter(string $paramteterValueGuid){
        $parameterValue = ParameterValue::find($paramteterValueGuid);

       /* return response()->json([
            'parameterValues' => new ParameterValueResource($parameterValues)
        ], 200);*/

        return $parameterValue;

    }

    public function updateParameter(array $parameterData){
        $paramteterValue = ParameterValue::find($parameterData['parameterValueId']);

        $paramteterValue->fill([
            'parameter_value' => $parameterData['parameterValue'],
            'description' => $parameterData['description'],
        ]);

        $paramteterValue->save();

        return $paramteterValue;

    }

    public function deleteParameter(string $paramteterValueId){
        $parameter = ParameterValue::find($paramteterValueId);
        $parameter->delete();
        /*return response()->json([
            'message' => 'parameter has been deleted!'
        ], 200);*/

    }

}
