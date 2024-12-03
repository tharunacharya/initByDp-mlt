<?php
/*
@copyright

Fleet Manager v6.1

Copyright (C) 2017-2022 Hyvikk Solutions <https://hyvikk.com/> All rights reserved.
Design and developed by Hyvikk Solutions <https://hyvikk.com/>

 */
// save your email config here:
return array(
    "driver" => "smtp",
    "host" => "smtp.hostinger.com",
    "port" => 465, // YOUR_MAIL_PORT
    "from" => array(
        "address" => "ets@mltcorporate.com", // FROM_EMAIL_ADDRESS
        "name" => "ETS", // FROM_USERNAME
    ),
    "username" => "ets@mltcorporate.com",
    "password" => "ETS@mlt0",
    "encryption" => "ssl", // E.G.: SSL/TLS/...
);
