<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * 指定した権限が付与されたAPIクライアントを返す
 * @return 権限が付与されたGoogle_Clientオブジェクト
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Gmail API PHP Quickstart');
    $client->setScopes(Google_Service_Gmail::MAIL_GOOGLE_COM); // ※1：スコープの設定
    $client->setAuthConfig('credentials.json');  // 取得したJSONファイルのパス
    $client->setAccessType('offline');

    // クライアント証明書ファイルが存在しない場合（初回実行時）は
    // 認証情報JSONファイルを用いて取得する。
    // 存在する場合（2回目以降の実行時）は、クライアント証明書を読み込む。
    $credentialsPath = 'token.json';  // クライアント証明書ファイルのパス
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // ユーザからの認証を行う
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // 認証コードをアクセストークンに変換する
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // クライアント証明書をファイルに保存する
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // クライアント証明書が有効期限切れの場合は更新し、ファイルへ保存しなおす
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * メイン処理
 */
// APIクライアントを作成し、サービスオブジェクトを作成する
//$client = getClient();
//$service = new Google_Service_Gmail($client);  // 使用するAPIごとのサービスオブジェクトを作成

// Print the labels in the user's account.
//$user = 'me';
//$results = $service->users_labels->listUsersLabels($user);
/*
if (count($results->getLabels()) == 0) {
  print "No labels found.\n";
} else {
  print "Labels:\n";
  foreach ($results->getLabels() as $label) {
    printf("- %s\n", $label->getName());
  }
}
*/
//gmail作成呼び出し
sendGmail($client);



//gmail作成送信関数
function sendGmail($client) {
    $data = "";
    $data .= "To:xxxx@xxx.xxx.xx\n";
    $data .= "Subject: メールたいとる\n";
    $data .= "\n"; // ヘッダーと本文を区切る空行
    $data .= "本文";

    $data = base64_encode($data); //base64エンコード

    $service = new Google_Service_Gmail($client);
    $msg = new Google_Service_Gmail_Message();
    $msg->setRaw($data);
    $result = $service->users_messages->send('me', $msg);
    return $result;
  }



  //人間の尊厳を考えない教師は嫌い