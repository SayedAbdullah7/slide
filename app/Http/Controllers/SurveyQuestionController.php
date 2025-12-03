<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyQuestionResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    use ApiResponseTrait;
    protected $surveyService;

    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function index()
    {
        $questions = $this->surveyService->questions();
        return $this->respondWithResource(SurveyQuestionResource::collection($questions), 'Survey questions retrieved successfully');

    }
}
