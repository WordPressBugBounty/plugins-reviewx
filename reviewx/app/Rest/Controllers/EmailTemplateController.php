<?php

namespace Rvx\Rest\Controllers;

use Rvx\Services\EmailService;
use Rvx\Utilities\Helper;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\Response;
class EmailTemplateController implements InvokableContract
{
    protected $emailService;
    /**
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @return Response
     */
    public function index()
    {
        $resp = $this->emailService->index();
        return Helper::getApiResponse($resp);
    }
    /**
     * @return Response
     */
    public function store($request)
    {
        try {
            $response = $this->emailService->store($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    /**
     * @return Response
     */
    public function show($request)
    {
        $id = $request->get_param('id');
        $resp = $this->emailService->show($id);
        return Helper::getApiResponse($resp);
    }
    public function update($request)
    {
        try {
            $response = $this->emailService->update($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function trash($request)
    {
        $id = $request->get_param('id');
        $resp = $this->emailService->trash($id);
        return Helper::getApiResponse($resp);
    }
    public function mailRequest($request)
    {
        $resp = $this->emailService->mailRequest($request->get_params());
        return Helper::getApiResponse($resp);
    }
    public function mailContents()
    {
        $resp = $this->emailService->content();
        return Helper::getApiResponse($resp);
    }
    public function saveEmailRequest($request)
    {
        try {
            $response = $this->emailService->saveEmailRequest($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function followup($request)
    {
        try {
            $response = $this->emailService->followup($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function photoReview($request)
    {
        try {
            $response = $this->emailService->photoReview($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function testMail($request)
    {
        try {
            $response = $this->emailService->testMail($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function markAsDone($request)
    {
        try {
            $response = $this->emailService->markAsComplete($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function requestEmailCancel($request)
    {
        try {
            $response = $this->emailService->emailCancel($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function requestEmailSend($request)
    {
        try {
            $response = $this->emailService->requestEmailSend($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function requestEmailResend($request)
    {
        try {
            $response = $this->emailService->requestEmailResend($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function requestEmailUnsubscribe()
    {
        try {
            $response = $this->emailService->requestEmailUnsubscribe();
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
    public function reviewRequestSettings()
    {
        $resp = $this->emailService->reviewRequestSettings();
        return Helper::getApiResponse($resp);
    }
    public function allReminderSettings($request)
    {
        try {
            $response = $this->emailService->allReminderSettings($request->get_params());
            return Helper::saasResponse($response);
        } catch (\Throwable $e) {
            return Helper::rvxApi(['error' => $e->getMessage()])->fails('This Action Fails', $e->getCode());
        }
    }
}
