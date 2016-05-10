<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ./");
    exit;
}
include_once 'config.php';
$bancheck = mysqli_query($db, "SELECT COUNT(id) as bans FROM bans WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' AND unban_by = 0;");
$bancheckresult = mysqli_fetch_assoc($bancheck);
if ($bancheckresult['bans'] > 0) {
    echo "You've been banned from accessing the admin panel, go away! :(";
    exit;
}
if (isset($_SESSION['login_id'])) {
    $loggedin = true;
    $loginerror = false;
} else {
    $loggedin = false;
    $loginerror = false;

    if (isset($_POST['submit-login'])) {
        $password = mysqli_real_escape_string($db, $_POST['password']);
        $captcha = mysqli_real_escape_string($db, $_POST['g-recaptcha-response']);
        $userip = $_SERVER['REMOTE_ADDR'];
        if ($userip == "::1") { //if ran on localhost
            $userip = $external_ip; //from config.php
        }

        $passwordquery = mysqli_query($db, "SELECT `value` FROM `settings` WHERE `setting` = 'password';");
        $passwordresult = mysqli_fetch_assoc($passwordquery);

        if ($passwordresult['value'] == "k" . sha1($password . "W4Y2sH0RT") . "m8") {
            //password correct
            $correctpass = 1;
        } else {
            //password incorrect
            $correctpass = 0;
            $loginerror = "Wrong password!";
        }

        $apicheck = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfEsQoTAAAAAAjFO8CrjZesedNfKRZvxofQn1Me&response=" . $captcha . "&remoteip=" . $userip);
        $data = json_decode($apicheck, true);

        if ($data['success'] == true) {
            //captcha worked
            $correctcaptcha = 1;
        } else {
            //captcha failed
            $correctcaptcha = 0;
            if ($data['error-codes'][0] == "missing-input-response") {
                $loginerror = "We were unable to find out wether you're human...";
            } else {
                $loginerror = $data['error-codes'][0];
            }
        }

        $postlogin = mysqli_query($db, "INSERT INTO `logins` (`captcha`, `password`, `ip`) VALUES (" . $correctcaptcha .", " . $correctpass . ", '" . $userip . "');");
        if (!$postlogin) {
            echo mysqli_error($db);
        } else {
            if ($correctpass && $correctcaptcha) {
                $loggedin = true;
                $_SESSION['ip'] = $userip;
                $_SESSION['login_id'] = mysqli_insert_id($db);
                $loginerror = false;
            }
        }
    }
}

if (isset($_GET['stats'])) {
    $action = 1;
    $id = mysqli_real_escape_string($db, $_GET['stats']);
    $view = "Stats: #" . $id;

    $getdetailsquery = "SELECT * FROM urls WHERE id = " . $id . " LIMIT 1;";
    $getdetails = mysqli_query($db, $getdetailsquery);
    $details = mysqli_fetch_assoc($getdetails);

    if ($details['active']) {
        $label = "success";
        $labeltxt = "Enabled";
    } else {
        $label = "danger";
        $labeltxt = "Disabled";
    }

    $getbaseurlquery = mysqli_query($db, "SELECT `value` FROM settings WHERE setting = 'base_url';");
    $getbaseurl = mysqli_fetch_assoc($getbaseurlquery);
    $baseurl = $getbaseurl['value'];

    if (isset($_GET['start'])) {
        $startdate = mysqli_real_escape_string($db, $_GET['start']);
        $enddate = mysqli_real_escape_string($db, $_GET['end']);
        $realstart = date("Y-m-d 00:00:00", strtotime($startdate));
        $realend = date("Y-m-d 23:59:59", strtotime($enddate));
        //echo $realstart . " | " . $realend;
        $where = " WHERE url_id = " . $id . " AND `datetime` BETWEEN '" . date("Y-m-d 00:00:00", strtotime($startdate)) . "' AND '" . date("Y-m-d 23:59:59", strtotime($enddate)) . "'";
    } else {
        $startdate = "";
        $enddate = "";
        $where = " WHERE url_id = " . $id;
    }


    $getclicksdayquery = "SELECT COUNT(ip) as c FROM visits WHERE url_id = " . $id . " AND datetime >= now() - INTERVAL 1 DAY;";
    $getclicksweekquery = "SELECT COUNT(ip) as c FROM visits WHERE url_id = " . $id . " AND datetime >= now() - INTERVAL 1 WEEK;";
    $getclicksmonthquery = "SELECT COUNT(ip) as c FROM visits WHERE url_id = " . $id . " AND datetime >= now() - INTERVAL 1 MONTH;";
    $getclicksquery = "SELECT COUNT(ip) as c FROM visits WHERE url_id = " . $id . ";";
    $getclicksday = mysqli_query($db, $getclicksdayquery);
    $getclicksweek = mysqli_query($db, $getclicksweekquery);
    $getclicksmonth = mysqli_query($db, $getclicksmonthquery);
    $getclicks = mysqli_query($db, $getclicksquery);
    $clicksday = mysqli_fetch_assoc($getclicksday);
    $clicksweek = mysqli_fetch_assoc($getclicksweek);
    $clicksmonth = mysqli_fetch_assoc($getclicksmonth);
    $clicks = mysqli_fetch_assoc($getclicks);

    $getuniqueclicksdayquery = "SELECT COUNT(DISTINCT ip) as c FROM visits WHERE url_id = " . $id . " AND datetime >= now() - INTERVAL 1 DAY;";
    $getuniqueclicksweekquery = "SELECT COUNT(DISTINCT ip) as c FROM visits WHERE url_id = " . $id . " AND datetime >= now() - INTERVAL 1 WEEK;";
    $getuniqueclicksmonthquery = "SELECT COUNT(DISTINCT ip) as c FROM visits WHERE url_id = " . $id . " AND datetime >= now() - INTERVAL 1 MONTH;";
    $getuniqueclicksquery = "SELECT COUNT(DISTINCT ip) as c FROM visits WHERE url_id = " . $id . ";";
    $getuniqueclicksday = mysqli_query($db, $getuniqueclicksdayquery);
    $getuniqueclicksweek = mysqli_query($db, $getuniqueclicksweekquery);
    $getuniqueclicksmonth = mysqli_query($db, $getuniqueclicksmonthquery);
    $getuniqueclicks = mysqli_query($db, $getuniqueclicksquery);
    $uniqueclicksday = mysqli_fetch_assoc($getuniqueclicksday);
    $uniqueclicksweek = mysqli_fetch_assoc($getuniqueclicksweek);
    $uniqueclicksmonth = mysqli_fetch_assoc($getuniqueclicksmonth);
    $uniqueclicks = mysqli_fetch_assoc($getuniqueclicks);

    $getdatesquery = "SELECT datetime, COUNT(id) as clicks FROM visits" . $where . " GROUP BY day(datetime) ORDER BY datetime ASC;";
    $getdates = mysqli_query($db, $getdatesquery);

    if (!$getdates) {
        echo mysqli_error($db);
    }
    $getstatsquery = "SELECT datetime, COUNT(id) as clicks FROM visits" . $where . " GROUP BY day(datetime) ORDER BY datetime ASC;";
    $getstats = mysqli_query($db, $getstatsquery);

    $getuniquestatsquery = "SELECT datetime, COUNT(DISTINCT ip) as clicks FROM visits" . $where . " GROUP BY day(datetime) ORDER BY datetime ASC;";
    $getuniquestats = mysqli_query($db, $getuniquestatsquery);
} else if (isset($_GET['toggle'])) {
    $action = 2;
    $id = mysqli_real_escape_string($db, $_GET['toggle']);
    $view = "Toggle: #" . $id;

    $getstatusquery = mysqli_query($db, "SELECT active FROM urls WHERE id = " . $id . ";");
    $getstatus = mysqli_fetch_assoc($getstatusquery);
    $status = $getstatus['active'];

    if ($status) {
        $updatequery = "UPDATE urls SET active = 0 WHERE id = " . $id . ";";
    } else {
        $updatequery = "UPDATE urls SET active = 1 WHERE id = " . $id . ";";
    }
    $update = mysqli_query($db, $updatequery);
    if (!$update) {
        echo mysqli_error($db);
    } else {
        header("Location: ./");
    }
    exit;
} else if (isset($_GET['settings'])) {
    $action = 3;
    $view = "Settings & security";

    if (isset($_POST['submit-settings'])){
        $baseurl = mysqli_real_escape_string($db, $_POST['baseurl']);
        $defaulturl = mysqli_real_escape_string($db, $_POST['defaulturl']);

        $updatebaseurlquery = mysqli_query($db, "UPDATE settings SET `value` = '" . $baseurl . "' WHERE setting = 'base_url';");
        $updatedefaulturlquery = mysqli_query($db, "UPDATE settings SET `value` = '" . $defaulturl . "' WHERE setting = 'default_url';");
        if (!$updatebaseurlquery || !$updatedefaulturlquery) {
            die(mysqli_error($db));
        }
        header("Location: ./?settings");
        exit;
    }

    if (isset($_POST['submit-password'])) {
        $oldpass = mysqli_real_escape_string($db, $_POST['passwordold']);
        $newpass = mysqli_real_escape_string($db, $_POST['password']);
        $confirm = mysqli_real_escape_string($db, $_POST['passwordconfirm']);

        $getcurrentpass = mysqli_query($db, "SELECT `value` FROM settings WHERE setting = 'password';");
        $currentpass = mysqli_fetch_assoc($getcurrentpass);
        if ($newpass != $confirm) {
            header("Location: ./?settings"); //new pass' didn't match
            exit;
        } else if (("k" . sha1($oldpass . "W4Y2sH0RT") . "m8") != $currentpass['value']) {
            header("Location: ./?settings"); //old pass incorrect
            exit;
        } else {
            $updatepassquery = mysqli_query($db, "UPDATE settings SET `value` = '" . "k" . sha1($newpass . "W4Y2sH0RT") . "m8" . "' WHERE setting = 'password';");
            session_destroy(); //log out
            header("Location: ./");
            exit;
        }
    }

    $baseurlquery = mysqli_query($db, "SELECT * FROM settings WHERE setting = 'base_url';");
    $baseurl = mysqli_fetch_assoc($baseurlquery);
    $defaulturlquery = mysqli_query($db, "SELECT * FROM settings WHERE setting = 'default_url';");
    $defaulturl = mysqli_fetch_assoc($defaulturlquery);
} else if (isset($_GET['ban'])) {
    $action = 4;
    $view = "Ban";
    $by = $_SESSION['login_id'];

    if (!isset($_GET['id'])) {
        $ip = mysqli_real_escape_string($db, $_GET['ban']);
        if (filter_var($ip, FILTER_VALIDATE_IP) == false) {
            $ip = "0.0.0.0";
        }
        $banquery = "INSERT INTO `bans` VALUES (null, '" . mysqli_real_escape_string($db, $ip) . "', null, " . $by . ", 0);";
    } else {
        $banquery = "UPDATE `bans` SET unban_by = '" . $by . "' WHERE id = '" . mysqli_real_escape_string($db, $_GET['id']) . "';";
    }

    $executeban = mysqli_query($db, $banquery);
    if (!$executeban) {
        echo mysqli_error($db);
    } else {
        header("Location: ?settings#bans");
        exit;
    }
} else {
    $action = 0;
    $view = "Dashboard";

    if (isset($_POST['submit'])) {
        $url = mysqli_real_escape_string($db, $_POST['url']);
        $desc = mysqli_real_escape_string($db, $_POST['description']);

        $insertquery = mysqli_query($db, "INSERT INTO urls VALUES (null, '" . $url . "', '" . $desc . "', 1);");
        if ($insertquery) {
            echo "Couldn't add redirect, sorry!";
        } else {
            header("Location: /");
        }
    }

    $getbaseurlquery = mysqli_query($db, "SELECT `value` FROM settings WHERE setting = 'base_url';");
    $getbaseurl = mysqli_fetch_assoc($getbaseurlquery);
    $baseurl = $getbaseurl['value'];

    $geturlsquery = mysqli_query($db, "SELECT * FROM urls;");
    if (!$geturlsquery) {
        echo "Important DB query failed";
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>URL Tracking Panel - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
    <div class="container">
        <h1>URL Tracking Panel <small><?= $view ?></small><div class="btn-group pull-right"><?php if ($view != "Dashboard"){ ?><a href="./" class="btn btn-primary">Back</a><?php } else { ?><a href="?settings" class="btn btn-primary">Settings</a> <?php } ?><a href="?logout" class="btn btn-danger">Log out</a></div></h1>
        <hr>
        <?php
        if (!$loggedin) { ?>
            <div class="col-sm-6 col-md-4">
                <form method="post" style="width: 303px;">
                    <div id="recaptcha" class="g-recaptcha" data-sitekey="6LfEsQoTAAAAALas6oayziBR8TPKmLibnOTJ-wNX"></div>
                    <input type="password" class="form-control" name="password" placeholder="**********" required>
                    <hr>
                    <input type="submit" class="btn btn-success" name="submit-login" value="Log in">
                </form>
            </div>
            <div class="col-sm-6 col-md-8">
                <br class="hidden-md hidden-lg">
                <div class="alert alert-<?php if (!$loginerror) { echo "info"; } else { echo "danger"; } ?>">
                    <?php if (!$loginerror) { ?>
                        Be sure to let us know you're human.
                        <br>Knowing the password is also quite useful :)
                    <?php } else { ?>
                        Logging you in didn't work:
                        <br><?= $loginerror ?>
                    <?php } ?>
                </div>
            </div>
        <?php } else {
            switch ($action) {
                default: //dashboard
                    ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>URL</th>
                            <th>Description</th>
                            <th>Clicks</th>
                            <th width="115">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($record = mysqli_fetch_assoc($geturlsquery)) {
                            if ($record['active']) {
                                $recordbtn = "success";
                                $recordbtntxt = "Enabled";
                            } else {
                                $recordbtn = "danger";
                                $recordbtntxt = "Disabled";
                            }
                            $getclicksquery = mysqli_query($db, "SELECT COUNT(ip) as c FROM visits WHERE url_id = " . $record['id'] . ";");
                            $getuniqueclicksquery = mysqli_query($db, "SELECT COUNT(DISTINCT ip) as c FROM visits WHERE url_id = " . $record['id'] . ";");
                            $getclicks = mysqli_fetch_assoc($getclicksquery);
                            $getuniqueclicks = mysqli_fetch_assoc($getuniqueclicksquery);
                            $clicks = $getclicks['c'];
                            $uniqueclicks = $getuniqueclicks['c'];
                            ?>
                            <tr>
                                <td><a href="../<?= $record['id'] ?>" target="_blank" title="<?= $baseurl . $record['id'] ?>"><?= $record['id'] ?></a></td>
                                <td><a href="<?= $record['url'] ?>" target="_blank"><?= $record['url'] ?></a></td>
                                <td><?= $record['description'] ?></td>
                                <td><span class="label label-info"><?= $uniqueclicks . " / " . $clicks ?></span></td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="?stats=<?= $record['id'] ?>" class="btn btn-primary">Stats</a>
                                        <a href="?toggle=<?= $record['id'] ?>" class="btn btn-<?= $recordbtn ?>"><?= $recordbtntxt ?></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } ?>
                        <tr>
                            <form method="post">
                            <td>-</td>
                            <td><input class="form-control input-sm" type="url" name="url" placeholder="http://domain.com/page"></td>
                            <td><input class="form-control input-sm" type="text" name="description" placeholder="Referer: page"></td>
                            <td>-</td>
                            <td><input class="btn btn-success btn-sm" type="submit" name="submit" value="Create"></td>
                            </form>
                        </tr>
                        </tbody>
                    </table>
                    <?php break;
                case 1: //stats
                    ?>
                    <div class="col-sm-6">
                        <h3>Details</h3>
                        <hr>
                        <dl class="dl-horizontal">
                            <dt>ID & Status</dt>
                            <dd><?= $id ?> <span class="label label-<?= $label ?>"><?= $labeltxt ?></span></dd>
                            <dt>Link</dt>
                            <dd><a href="<?= $baseurl . $id ?>"
                                   target="_blank"><?= $baseurl . $id ?></a></dd>
                            <dt>URL</dt>
                            <dd><a href="<?= $details['url'] ?>" target="_blank"><?= $details['url'] ?></a></dd>
                            <dt>Description</dt>
                            <dd><?= $details['description'] ?></dd>
                        </dl>
                    </div>
                    <div class="col-sm-6">
                        <h3>Clicks
                            <small>(total)</small>
                        </h3>
                        <hr>
                        <dl class="dl-horizontal">
                            <dt><a href="?stats=<?= $id ?>&start=<?= date("Y-m-d") ?>&end=<?= date("Y-m-d") ?>" title="View graph for today">Today</a></dt>
                            <dd><?= $uniqueclicksday['c'] . " / " . $clicksday['c'] ?></dd>
                            <dt><a href="?stats=<?= $id ?>&start=<?= date("Y-m-d", strtotime("-1 weeks")) ?>&end=<?= date("Y-m-d") ?>" title="View graph for this week">This week</a></dt>
                            <dd><?= $uniqueclicksweek['c'] . " / " . $clicksweek['c'] ?></dd>
                            <dt><a href="?stats=<?= $id ?>&start=<?= date("Y-m-d", strtotime("-1 months")) ?>&end=<?= date("Y-m-d") ?>" title="View graph for this month">This month</a></dt>
                            <dd><?= $uniqueclicksmonth['c'] . " / " . $clicksmonth['c'] ?></dd>
                            <dt><a href="?stats=<?= $id ?>" title="View graph">Forever</a></dt>
                            <dd><?= $uniqueclicks['c'] . " / " . $clicks['c'] ?></dd>
                        </dl>
                    </div>
                    <div class="col-sm-12">
                        <h3>Statistics
                            <small>
                                <form method="get" class="form-inline pull-right"><input type="hidden" name="stats" value="<?= $id ?>"><label for="input-start">From</label> <input id="input-start" name="start" class="form-control hasdatepicker" value="<?= $startdate ?>" type="text" placeholder="Start"> <label for="input-end">to</label> <input id="input-end" name="end" class="form-control hasdatepicker" value="<?= $enddate ?>" type="text" placeholder="End"> <input type="submit" class="btn btn-primary" value="Go"></form>
                            </small>
                        </h3>
                        <hr>
                        <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                    </div>
                    <?php break; //case 2 missing because toggle redirects to dashboard (1)
                case 3: //settings
                    ?>
                    <form method="post" action="?settings" class="row">
                        <div class="col-sm-4">
                            <label for="input-baseurl">Base URL</label>
                            <input type="url" id="input-baseurl" name="baseurl" placeholder="http://domain.com/url/" value="<?= $baseurl['value'] ?>" class="form-control">
                        </div><div class="col-sm-4">
                            <label for="input-defaulturl">Default redirect</label>
                            <input type="url" id="input-defaulturl" name="defaulturl" placeholder="http://domain.com/page/" value="<?= $defaulturl['value'] ?>" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label for="submit-settings">Save changes</label>
                            <input type="submit" id="submit-settings" name="submit-settings" class="btn btn-success form-control">
                        </div>
                    </form>
                    <hr>
                    <form method="post" action="?settings" class="row">
                        <div class="col-sm-3">
                            <label for="input-passwordold">Old password</label>
                            <input type="password" id="input-baseurl" name="passwordold" placeholder="**********" class="form-control">
                        </div>
                        <div class="col-sm-3">
                            <label for="input-password">New password</label>
                            <input type="password" id="input-baseurl" name="password" placeholder="**********" class="form-control">
                        </div>
                        <div class="col-sm-3">
                            <label for="input-passwordconfirm">Confirm new password</label>
                            <input type="password" id="input-passwordconfirm" name="passwordconfirm" placeholder="**********" class="form-control">
                        </div>
                        <div class="col-sm-3">
                            <label for="submit-password">Save changes</label>
                            <input type="submit" id="submit-password" name="submit-password" class="btn btn-success form-control">
                        </div>
                    </form>
                    <hr>
                    <h3>Logins <small>Last 25: <span class="bg-info">You</span> <span class="bg-danger">Banned</span></small></h3>
                    <hr>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td>#ID</td>
                            <td>Datetime</td>
                            <td>IP</td>
                            <td>Country</td>
                            <td>Captcha</td>
                            <td>Password</td>
                            <td>Ban IP</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $logquery = mysqli_query($db, "SELECT * FROM `logins` ORDER BY `datetime` DESC LIMIT 25");
                        $bannedlogins = [];
                        $notbannedlogins = [];

                        while ($logresult = mysqli_fetch_array($logquery)) {
                            if (!in_array($logresult['ip'], $bannedlogins) && !in_array($logresult['ip'], $notbannedlogins)) {
                                $checkbanquery = "SELECT id FROM `bans` WHERE ip = '" . $logresult['ip'] . "' AND unban_by = 0;";
                                $banquery = mysqli_query($db, $checkbanquery);
                                $banned = mysqli_fetch_assoc($banquery);
                                if (isset($banned['id'])) {
                                    $banned = true;
                                } else {
                                    $banned = false;
                                }
                            }
                            if ($logresult['id'] != 0) {
                                if ($logresult['captcha']) {
                                    $marker1 = "success";
                                    $marker2 = "ok";
                                } else {
                                    $marker1 = "danger";
                                    $marker2 = "remove";
                                }
                                if ($logresult['password']) {
                                    $marker3 = "success";
                                    $marker4 = "ok";
                                } else {
                                    $marker3 = "danger";
                                    $marker4 = "remove";
                                }
                                ?>
                                <tr class="<?php if ($logresult['id'] == $_SESSION['login_id']) { echo "info"; } else if ($banned) { echo "danger"; } ?>">
                                    <td><?= $logresult['id'] ?></td>
                                    <td><?= $logresult['datetime'] ?></td>
                                    <td><?= $logresult['ip'] ?></td>
                                    <td><img class="country-flag" src="http://www.geojoe.co.uk/api/flag/?ip=<?= $logresult['ip'] ?>" alt="Flag of <?= $logresult['ip'] ?>"></td>
                                    <td><span class="label label-<?= $marker1 ?>"><i class="glyphicon glyphicon-<?= $marker2 ?>"></i></span></td>
                                    <td><span class="label label-<?= $marker3 ?>"><i class="glyphicon glyphicon-<?= $marker4 ?>"></i></span></td>
                                    <td><?php if ($banned) { echo "-"; } else { ?><a class="btn btn-xs btn-danger" href="?ban=<?= $logresult['ip'] ?>"><i class="glyphicon glyphicon-fire"></i></a><?php } ?></td>
                                </tr>
                                <?php
                            }
                        } ?>
                        </tbody>
                    </table>
                    <hr>
                    <h3>IP-Bans <small>All: <span class="bg-info">You</span> <span class="bg-danger">Banned</span> <span class="bg-success">Unbanned</span> <form method="get" action="./" class="form-inline pull-right"><input id="input-ip" name="ban" class="form-control" type="text" placeholder="127.0.0.1"> <input type="submit" class="btn btn-danger" value="BAN"></form></small></h3>
                    <hr>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td>#ID</td>
                            <td>Datetime</td>
                            <td>IP</td>
                            <td>By <small>login id & ip</small></td>
                            <td>Unban by <small>login id & ip</small></td>
                            <td>Unban</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $logquery = mysqli_query($db, "SELECT bans.*,by_logins.ip AS by_ip,unban_logins.ip AS unban_ip FROM bans INNER JOIN logins AS by_logins ON bans.ban_by = by_logins.id INNER JOIN logins AS unban_logins ON bans.unban_by = unban_logins.id ORDER BY bans.datetime DESC");
                        while ($logresult = mysqli_fetch_array($logquery)) { ?>
                            <tr class="<?php if ($logresult['unban_by'] == 0) { echo "danger"; } else { echo "success"; } ?>">
                                <td><?= $logresult['id'] ?></td>
                                <td><?= $logresult['datetime'] ?></td>
                                <td><?= $logresult['ip'] ?> <img class="country-flag" src="http://www.geojoe.co.uk/api/flag/?ip=<?= $logresult['ip'] ?>" alt="Flag of <?= $logresult['ip'] ?>"></td>
                                <td<?php if ($logresult['ban_by'] == $_SESSION['login_id']) { echo " class=\"info\""; } ?>>[<?= $logresult['ban_by'] ?>] <?= $logresult['by_ip'] ?> <img class="country-flag" src="http://www.geojoe.co.uk/api/flag/?ip=<?= $logresult['by_ip'] ?>" alt="Flag of <?= $logresult['by_ip'] ?>"></td>
                                <td<?php if ($logresult['unban_by'] == $_SESSION['login_id']) { echo " class=\"info\""; } ?>><?php if ($logresult['unban_by'] == 0) { echo "-"; } else { ?><?= "[" . $logresult['unban_by'] . "] " . $logresult['unban_ip'] ?>  <img class="country-flag" src="http://www.geojoe.co.uk/api/flag/?ip=<?= $logresult['unban_ip'] ?>" alt="Flag of <?= $logresult['unban_ip'] ?>"><?php } ?></td>
                                <td><?php if ($logresult['unban_by'] == 0) { ?><a class="btn btn-xs btn-success" href="?ban&id=<?= $logresult['id'] ?>"><i class="glyphicon glyphicon-fire"></i></a><?php } else { echo "Unbanned"; } ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php break;
            }
        }
        ?>
    </div>
    <script type="text/javascript" src="../assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../assets/js/login.js"></script>
    <?php if ($action == 1) { ?>
    <script type="text/javascript" src="../assets/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../assets/js/highstock.js"></script>
    <script type="text/javascript" src="../assets/js/highcharts-more.js"></script>
    <script type="text/javascript" src="../assets/js/stats.js"></script>
    <script type="text/javascript">
        var clicks = [<?php
        $i = 0;
        while ($stats = mysqli_fetch_assoc($getstats)) {
        if ($i != 0) { echo ","; }
        echo $stats['clicks'];
        $i = 1;
        }
        ?>],
            unique = [<?php
        $i = 0;
        while ($stats = mysqli_fetch_assoc($getuniquestats)) {
        if ($i != 0) { echo ","; }
        echo $stats['clicks'];
        $i = 1;
        }
        ?>],
            dates = [<?php
        $i = 0;
        while ($stats = mysqli_fetch_assoc($getdates)) {
        if ($i != 0) { echo ","; }
        echo "'" . date("d-m-Y", strtotime($stats['datetime'])) . "'";
        $i = 1;
        }
        ?>];

        //var chartdata = [{'name' :'Total', 'data' : data1},{'name' : 'Unique', 'data': data2}];
        createChart(dates, clicks, unique, <?= $id ?>, '<?= $startdate ?>', '<?= $enddate ?>');
    </script>
    <?php } ?>
</body>
</html>