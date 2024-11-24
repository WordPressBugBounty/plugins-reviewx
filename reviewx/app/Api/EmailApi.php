<?php

namespace Rvx\Api;

use Rvx\Apiz\Http\Response;
use Exception;
class EmailApi extends \Rvx\Api\BaseApi
{
    /**
     * @return Response
     * @throws Exception
     */
    public function index() : Response
    {
        return $this->get('email-templates');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function show($id) : Response
    {
        return $this->get('email-templates' . $id);
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function create(array $data) : Response
    {
        return $this->withJson($data)->post('email-templates');
    }
    /**
     * @param array $data
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function update(array $data, $uid) : Response
    {
        return $this->withJson($data)->put('email-templates/' . $uid);
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function remove($uid) : Response
    {
        return $this->delete('email-templates/' . $uid);
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function reviewRequest($uid) : Response
    {
        return $this->get('review/request/emails' . $uid);
    }
    /**
     * @param $uid
     * @return Response
     * @throws Exception
     */
    public function mailRequest($data) : Response
    {
        if (!empty($data)) {
            $callableRoute = 'review/request/emails';
            $callableRoute .= '?' . $data;
            return $this->get($callableRoute);
        }
        return $this->get('review/request/emails');
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function content() : Response
    {
        return $this->get('review/email/contents');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function saveEmailRequest(array $data) : Response
    {
        return $this->withJson($data)->post('review/email/request/contents');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function followup(array $data) : Response
    {
        return $this->withJson($data)->post('review/email/followup/contents');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function photoReview(array $data) : Response
    {
        return $this->withJson($data)->post('review/email/photo/contents');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function testMail(array $data) : Response
    {
        return $this->withJson($data)->post('review/email/send-test');
    }
    /**
     * @param string $uid
     * @return Response
     * @throws Exception
     */
    public function emailCancel($uid) : Response
    {
        return $this->withJson()->put('/review-request/email/cancel/' . $uid);
    }
    /**
     * @param string $uid
     * @return Response
     * @throws Exception
     */
    public function requestEmailSend($data) : Response
    {
        $callableRoute = '/review-request/email/send/' . $data['uid'];
        return $this->withJson($data)->put($callableRoute);
    }
    /**
     * @param string $uid
     * @return Response
     * @throws Exception
     */
    public function requestEmailResend($data) : Response
    {
        $callableRoute = 'review-request/email/resend/' . $data['uid'];
        return $this->withJson($data)->put($callableRoute);
    }
    /**
     * @param string $uid
     * @return Response
     * @throws Exception
     */
    public function requestEmailUnsubscribe() : Response
    {
        return $this->withJson()->put('review-request/unsubscribe/');
    }
    /**
     * @param string $uid
     * @return Response
     * @throws Exception
     */
    public function markAsComplete($uid) : Response
    {
        return $this->withJson()->put('/review-request/email/mark-as-done/' . $uid);
    }
    /**
     * @return Response
     * @throws Exception
     */
    public function reviewRequestSettings() : Response
    {
        return $this->get('review/request/settings');
    }
    /**
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function allReminderSettings(array $data) : Response
    {
        if ($data['is_default'] == \true) {
            return $this->withJson($data)->post('review/request/settings/?' . $data['is_default']);
        }
        return $this->withJson($data)->post('review/request/settings');
    }
}
