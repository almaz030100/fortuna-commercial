<?php
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
   die("error");
}
// Pre settings
define("SND_FROM", "info@франшиза-сервис.рф");
define("SND_TO", "rta@fte.ru"); // allowble comma-sepparated values
define("SND_NAME", "Fortuna");
define("NAME_FRANCH", "Фортуна");
define("SMTP", false); // see settings in Helper.php before you change this const
define("SND_TO_BITRIX", false); // Разрешить отправку лидов в Битрикс. true - отправляем, false - запрещаем


$path = dirname(__FILE__);
require $path .'/Hellper.php';

$domainName = idn_to_utf8($_SERVER['HTTP_HOST']);

// Structure of array: $arr["NAME_OF_FORM_FIELD"] = array(0..1=>"Field name translations",2=>"Field value")
$fields = array(
	"section-btn-text"	=>	["Текст на кнопке","Answertext",false],
	"section-name-text"	=>	["Заголовок на экране, с которого оставлена заявка","Section-name-text",false],
	"name"	=>	["Имя отправителя","Name",false],
	"phone"	=>	["Номер телефона","Phone",false],
	"email"	=>	["Email","Email",false],
	"section-name"	=>	["Тип формы","Section-name",false],
	"page_url"	=>	["Url страницы, с которого пришла заявка","Page URL",false],
	"utm_source"	=>	["Источник трафика","utm_source",false],
	"utm_medium"	=>	["Тип рекламы","utm_medium",false],
	"utm_placement"	=>	["Место показа", "utm_placement",false],
	"utm_description"	=>	["Текст рекламного объявления","utm_description",false],
	"utm_term"	=>	["Ключевое слово","utm_term",false],
	"device_type"	=>	["Тип устройства","device_type",false],
	"utm_campaign_name"	=>	["Название рекламного кабинета","utm_campaign_name",false],
	"utm_campaign"	=>	["Номер рекламной кампании","utm_campaign",false],
	"user_location_ip"	=>	["Страна (по ip-адресу), регион, город", "user_location_ip", false],
	"city"	=>	["Город","City",false],
);


$thankYouPage = false;
foreach ($_REQUEST as $reqFieldName => $value) {
	if ($reqFieldName == "thank_you")
	{
		$thankYouPage = true;
		continue;
	}
	if (isset($fields[$reqFieldName]))
		$fields[$reqFieldName][2] = strip_tags($value);
}

/**
 * Указываются имена полей относящихся к группе
 */
$groups = [
	"1) Информация, указанная посетителем сайта:" =>[
		"fields" =>["name", "phone", "email", "city"],
		"html"=>""
	],
	"2) Информация из рекламной системы:" => [
		"fields" => ["page_url", "utm_source", "utm_medium", "utm_placement", "utm_description", "utm_term", "device_type", "utm_campaign_name", "utm_campaign"],
		"html" => ""
	],
	"3) Кастомная информация:" => [
		"fields" => ["section-btn-text", "section-name-text", "section-name", "user_location_ip"],
		"html" => ""
	],
];


foreach ($fields as $key => $val) {
	if (!$val[2] || empty($val[2])) continue;

	if (in_array($key, $groups["1) Информация, указанная посетителем сайта:"]["fields"])) {
		$groups["1) Информация, указанная посетителем сайта:"]["html"] .=
		'<p style=""><strong>' . $val[0] . ":</strong> " . trim($val[2]) . "</p>\r\n";
	}

	if (in_array($key, $groups["2) Информация из рекламной системы:"]["fields"])) {
		$groups["2) Информация из рекламной системы:"]["html"] .=
		'<p style=""><strong>' . $val[0] . ":</strong> " . trim($val[2]) . "</p>\r\n";
	}

	if (in_array($key, $groups["3) Кастомная информация:"]["fields"])) {
		$groups["3) Кастомная информация:"]["html"] .=
		'<p style=""><strong>' . $val[0] . ":</strong> " . trim($val[2]) . "</p>\r\n";
	}	
}
// Create mail data
$headers  = "From: <". SND_FROM.">" . "\r\n";
$headers .= "Reply-To: ".SND_FROM. "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html;charset=utf-8;\r\n";
$headers .= "X-Priority: 3\r\n";
$headers .= "X-Mailer: PHP". phpversion() ."\r\n";

$subject = "Заявка на франшизу «". NAME_FRANCH."»";

$phone = "";
$nameOrCity = "";
$htmlBody  = "<html><body style='font-family:Arial,sans-serif;'5>";
$htmlBody .= "<h2>Вам поступила новая заявка на франшизу «". NAME_FRANCH ."»</h2>\r\n";

// $htmlBody .= '<p><strong>Домен:</strong> ' . $domainName . '</pr>' . "\r\n";

foreach ($groups as $sectionTitle => $value) {
	if (empty($value["html"])) continue;
	$htmlBody .= '<h3 style="font-size: 15px; font-weight: normal; font-style: italic; padding: 3px 7px; background: rgba(0,0,0,0.1);">'.$sectionTitle.'</h3>';
	$htmlBody .=  $value["html"];
}


$htmlBody .= '<p style="font-style: italic; padding: 10px 0 0 0;">Свяжитесь с потенциальным покупателем в течение 15 минут!</p>';
$htmlBody .= "</body></html>";

$goodStatus = ($thankYouPage) ? 2 : 1;

try {
	if (SND_TO_BITRIX) {
		require_once('../bx/crest.php');

		$leadRsp = CRest::call(
			'crm.lead.add',
			[
				'fields' => [
					"TITLE" => "«Чистая польза» ".$fields["name"][2],
					"NAME" => $fields["name"][2],
					"SECOND_NAME" => "",
					"LAST_NAME" => "",
					"STATUS_ID" => "NEW",
					"OPENED" => "Y",
					"ASSIGNED_BY_ID" => 1,
					"PHONE" => [["VALUE" => trim($fields["phone"][2]), "VALUE_TYPE" => "WORK"]],
					"EMAIL" => [["VALUE" => trim($fields["email"][2]), "VALUE_TYPE" => "WORK"]],
					"UF_CRM_1650794493" => "https://xn--80aajpfe0aectbl3b7b.xn--p1ai/project/chist-polza/",
					"SOURCE_ID" => "CALL",
					"UF_CRM_PROPERTY_NOVOE_POLE"=> "10689",
					
				],
				"params" =>  ["REGISTER_SONET_EVENT" => "Y"]
			]
		);
	}

} catch (Exception $e) {}

if (mailer(SND_TO, $subject, $htmlBody, $headers))
{
	if (file_exists("customerEmailTPL.php") && $goodStatus == 1 && !empty($fields["email"][2]))
	{

		$data = array(
			"name" => $fields["name"][2],
			"city" => $fields["city"][2]
		);

		$preName = (!empty($data["name"])) ? $data["name"].", спасибо" : "Спасибо";
		$customerSubject = "{$preName}, что оставили заявку на франшизу «". NAME_FRANCH."»";
		// Можно назначить произвольный заголовок для письма клиенту
		$customerBody = fileContentsToVar("customerEmailTPL.php", $data);

		$headers  = "From: <". SND_FROM.">" . "\r\n";
		$headers .= "Reply-To: ". SND_FROM . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html;charset=utf-8;\r\n";
		$headers .= "X-Priority: 3\r\n";
		$headers .= "X-Mailer: PHP" . phpversion() . "\r\n";

		mailer($fields["email"][2], $customerSubject, $customerBody, $headers);
	}
	echo $goodStatus;
}
else
	echo 1;
