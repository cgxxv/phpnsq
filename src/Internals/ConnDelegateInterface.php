<?php

namespace OkStuff\PHPNSQ\Internals;

interface ConnDelegate
{
    // OnResponse is called when the connection
    // receives a FrameTypeResponse from nsqd
    public function onResponse($connector, $data);

    // OnError is called when the connection
    // receives a FrameTypeError from nsqd
    public function onError($connector, $data);

    // OnMessage is called when the connection
    // receives a FrameTypeMessage from nsqd
    public function onMessage($connector, $message);

    // OnMessageFinished is called when the connection
    // handles a FIN command from a message handler
    public function onMessageFinished($connector, $message);

    // OnMessageRequeued is called when the connection
    // handles a REQ command from a message handler
    public function onMessageRequeued($connector, $message);

    // OnBackoff is called when the connection triggers a backoff state
    public function onBackoff($connector);

    // OnContinue is called when the connection finishes a message without adjusting backoff state
    public function onContinue($connector);

    // OnResume is called when the connection triggers a resume state
    public function onResume($connector);

    // OnIOError is called when the connection experiences
    // a low-level TCP transport error
    public function onIOError($connector, $error);

    // OnHeartbeat is called when the connection
    // receives a heartbeat from nsqd
    public function onHeartbeat($connector);

    // OnClose is called when the connection
    // closes, after all cleanup
    public function onClose($connector);
}