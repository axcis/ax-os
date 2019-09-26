<?php
//お知らせメール
$lang['notice_mail_config'] = array(
		"protocol" => "smtp",
		"smtp_host" => "sv8606.xserver.jp",
		"smtp_user" => "system@axcis.co.jp",
		"smtp_pass" => "Axcissys01",
		"smtp_port" => 587,
		"smtp_timeout" => 10
);
$lang['notice_mail_from'] = "system@axcis.co.jp";
$lang['notice_mail_to'] = "system@axcis.co.jp";
$lang['notice_mail_cc'] = "";
//TODO 本番環境設定
$lang['notice_subject'] = "お知らせが掲載されました。";
$lang['notice_msg'] =<<<Body

各位

以下のお知らせを掲載しました。ご確認ください。
(アクオスのトップページにも掲載されています。)

━━━━━━━━━━━━━━━━━

【タイトル】
[%0]

【お知らせ内容】
[%1]

【重要度】
[%2]

【掲載期限】
[%3]

【出欠確認期限】
[%4]

━━━━━━━━━━━━━━━━━
Body;

//会議室予約通知メール
$lang['conference_appoint_mail_config'] = array(
		"protocol" => "smtp",
		"smtp_host" => "sv8606.xserver.jp",
		"smtp_user" => "system@axcis.co.jp",
		"smtp_pass" => "Axcissys01",
		"smtp_port" => 587,
		"smtp_timeout" => 10
);
$lang['conference_appoint_mail_from'] = "system@axcis.co.jp";
$lang['conference_appoint_mail_to'] = "system@axcis.co.jp";
$lang['conference_appoint_mail_cc'] = "";
//TODO 本番環境設定
$lang['conference_appoint_subject'] = "会議室予約通知メール";
$lang['conference_appoint_msg'] =<<<Body
[%0]さんより、会議室の予約申請がありました。

━━━━━━━━━━━━━━━━━

【予約日】
[%1]

【場所】
[%2]

【使用時間】
[%3]～[%4]

【利用目的】
[%5]

━━━━━━━━━━━━━━━━━
Body;
?>