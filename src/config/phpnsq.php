<?php

return [
    "nsq" => [
        "nsqd-addrs" => [
            "127.0.0.1:4150",
            // "192.168.33.10:4150",
        ],
        "logdir" => "/tmp",
        "auth_secret" => "secret",
        //FIXME:
        // "tls_config" => [
        //     "local_cert" => "/home/vagrant/docker/nsqio/certs/client.pem",
        //     "local_pk" => "/home/vagrant/docker/nsqio/certs/client.key",
        //     "cafile" => "/home/vagrant/docker/nsqio/certs/ca.pem",
        //     "passphrase" => "test", //if your cert has a passphrase
        //     "cn_match" => "test",
        //     "peer_fingerprint" => "sha256",
        // ],
    ],
];
