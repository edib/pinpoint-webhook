<?php

$ini = parse_ini_file("../resources/config.ini");

function isValidJSON($str)
{
    json_decode($str);
    return json_last_error() == JSON_ERROR_NONE;
}

function write_to_somewhere($decoded_object)
{
    $myfile = fopen("json_content.json", "a") or die("Unable to open file!");
    fwrite($myfile, date("Y-m-d H:i:s") . " " . $decoded_object . "\n");
    fclose($myfile);
}

function card_template($ds)
{
    $title = $ds["applicationId"] . " / " . $ds["checker"]["name"];
    $dv = $ds["checker"]["detectedValue"];
    $alarm_type = $ds["checker"]["type"];
    $content = "";
    if ($alarm_type == "LongValueAgentChecker") {
        foreach ($dv as $value) {
            $url = $ds["pinpointUrl"] . '/inspector/' . $ds["applicationId"] . '@' . $ds["serviceType"] . '/5m/' . date("Y-m-d-H-i") . "/" . $value["agentId"];
            $content .= '{ "type": "TextBlock", "text": "[Agent: ' . $value["agentId"] . ", Value: " . $value["agentValue"] . '](' . $url . ')"},';
        }
    } else if ($alarm_type == "LongValueAlarmChecker") {
        $url = $ds["pinpointUrl"] . "/main/" . $ds["applicationId"] . "@" . $ds["serviceType"] . "/5m/" . date("Y-m-d-H-i");
        $content .= '{ "type": "TextBlock", "text": "[' . $dv . '](' . $url . ')"},';
    }
    return '{
        "type":"message",
        "attachments":[
           {
              "contentType":"application/vnd.microsoft.card.adaptive",
              "content":{
                 "type":"AdaptiveCard",
                 "version":"1.2",
                 "body":[
                    {
                        "type": "TextBlock",
                        "size": "large",
                        "text": "Pinpoint Alarm"
                        },
                     {
                     "type": "TextBlock",
                     "size": "large",
                     "text": "' . $title . '"
                     },
                     ' . $content . '
                 ]
              }
           }
        ]
     }';
}

if (isset($_POST)) {
    $data_string = file_get_contents("php://input");

    if (strlen($data_string) > 0 && isValidJSON($data_string)) {

        write_to_somewhere($data_string);

        $ds = json_decode($data_string, true);

        $group_teams_url = $ds["userGroup"]["userGroupId"];

        if (isset($ini[$group_teams_url])) {
            $url = $ini[$group_teams_url];
        } else {
            $url = $ini["test"];
        }

        $curl_string = card_template($ds);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_string);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type:application/json',
                'Content-Length: ' . strlen($curl_string),
            )
        );

        $result = curl_exec($ch);
        curl_close($ch);

    }
}
