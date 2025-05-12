<?php

namespace Rvx\Services;

use Rvx\Apiz\Http\Response;
use Rvx\Api\EmailApi;
class EmailService extends \Rvx\Services\Service
{
    /**
     * @return Response
     */
    public function index()
    {
        return (new EmailApi())->index();
    }
    /**
     * @return Response
     */
    public function store($data)
    {
        return (new EmailApi())->create($data);
    }
    /**
     * @return Response
     */
    public function update($data)
    {
        return (new EmailApi())->update($data);
    }
    /**
     * @return Response
     */
    public function remove($request)
    {
        return (new EmailApi())->remove();
    }
    public function mailRequest($data)
    {
        $query = \http_build_query($data);
        return (new EmailApi())->mailRequest($query);
    }
    public function content()
    {
        return (new EmailApi())->content();
    }
    public function saveEmailRequest($data)
    {
        return (new EmailApi())->saveEmailRequest($data);
    }
    public function followup($data)
    {
        return (new EmailApi())->followup($data);
    }
    public function photoReview($data)
    {
        return (new EmailApi())->photoReview($data);
    }
    public function testMail($data)
    {
        return (new EmailApi())->testMail($data);
    }
    public function markAsComplete($data)
    {
        return (new EmailApi())->markAsComplete($data['uid']);
    }
    public function emailCancel($data)
    {
        return (new EmailApi())->emailCancel($data['uid']);
    }
    public function requestEmailSend($data)
    {
        return (new EmailApi())->requestEmailSend($data);
    }
    public function requestEmailResend($data)
    {
        return (new EmailApi())->requestEmailResend($data);
    }
    public function requestEmailUnsubscribe()
    {
        return (new EmailApi())->requestEmailUnsubscribe();
    }
    public function reviewRequestSettings()
    {
        return (new EmailApi())->reviewRequestSettings();
    }
    public function allReminderSettings($data)
    {
        return (new EmailApi())->allReminderSettings($data);
    }
}
