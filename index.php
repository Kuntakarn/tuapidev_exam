<?php

require "vendor/autoload.php";
require_once('vendor/linecorp/line-bot-sdk/line-bot-sdk-tiny/LINEBotTiny.php');

$access_token = '{Access Tokek}'; 

// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
    // Loop through each event
    foreach ($events['events'] as $event) {
        // Reply only when message sent is in 'text' format
        if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
            // Get text sent
            $text = $event['message']['text'];
            $replyToken = $event['replyToken'];



            $ex = explode('-', $text);

            switch (strtolower($ex[0])) {
                case 'e':

                    $obj = get_data_emp($ex[1], $replyToken);
                    if ($obj) {
                        $post = $obj;
                    } else {
                        $messages = [
                            'type' => 'text',
                            'text' => 'ข้ออภัยไม่พบข้อมูล บุคลากร กรุณากรอกข้อมูลให้ถูกต้อง'
                        ];
                        $data = [
                            'replyToken' => $replyToken,
                            'messages' => [$messages],
                        ];
                        $post = json_encode($data);
                    }
                    break;
                case 's':
                    $obj = get_data_std($ex[1], $replyToken);
                    if ($obj) {
                        $post = $obj;
                    } else {
                        $messages = [
                            'type' => 'text',
                            'text' => 'ข้ออภัยไม่พบข้อมูล นักศึกษา กรุณากรอกข้อมูลให้ถูกต้อง'
                        ];
                        $data = [
                            'replyToken' => $replyToken,
                            'messages' => [$messages],
                        ];
                        $post = json_encode($data);
                    }
                    break;
                default:

                    $text = 'กรุณากรอกข้อมูลให้ถูกต้อง'
                            . "\r\n กรณีพนักงาน : e-{username}"
                            . "\r\n กรณีนักศึกษา : s-{student id}";

                    $messages = [
                        'type' => 'text',
                        'text' => $text
                    ];
                    $data = [
                        'replyToken' => $replyToken,
                        'messages' => [$messages],
                    ];
                    $post = json_encode($data);
                    break;
            }




            $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);
            $url = 'https://api.line.me/v2/bot/message/reply';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            echo $result . "\r\n";
        }
    }
}

function get_data_emp($username = NULL, $replyToken = NULL) {
    if (!empty($username)and ! empty($replyToken)) {


        $url = 'https://restapi.tu.ac.th/api/v1/profile/emp/info/?username=' . $username;
        $headers = array('Content-Type: application/json', 'Application-Key: {Token Key From TU APIs}');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result);
        if (!empty($data) and ! empty($data->displayname_th)) {

            $rs['username'] = $data->username;
            $rs['displayname_th'] = $data->displayname_th;
            $rs['displayname_en'] = $data->displayname_en;
            $rs['employee_type'] = $data->employee_type;
            $rs['organization'] = $data->organization;
            $rs['department'] = $data->department;


            $messages = '
                                        {
                                        "replyToken":"' . $replyToken . '",
                                        "messages":[ ';

            $messages .= '
                                            {
                                                "type": "flex",
                                                "altText": "Flex Message",
                                                "contents": {
                                                  "type": "bubble",
                                                  "body": {
                                                    "type": "box",
                                                    "layout": "vertical",
                                                    "spacing": "md",
                                                    "action": {
                                                      "type": "uri",
                                                      "label": "Action",
                                                      "uri": "https://restapi.tu.ac.th"
                                                    },
                                                    "contents": [
                                                      {
                                                        "type": "text",
                                                        "text": "Getting Employee Info",
                                                        "size": "md",
                                                        "weight": "bold"
                                                      },
                                                      {
                                                        "type": "separator"
                                                      },
                                                      {
                                                        "type": "box",
                                                        "layout": "vertical",
                                                        "spacing": "sm",
                                                        "contents": [
                                                          {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                              {
                                                                "type": "text",
                                                                "text": "username",
                                                                "flex": 0,
                                                                "margin": "sm",
                                                                "weight": "regular"
                                                              },
                                                              {
                                                                "type": "text",
                                                                "text": "' . $rs['username'] . '",
                                                                "size": "sm",
                                                                "align": "end",
                                                                "color": "#AAAAAA"
                                                              }
                                                            ]
                                                          },
                                                          {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                              {
                                                                "type": "text",
                                                                "text": "Name Thai",
                                                                "flex": 0,
                                                                "margin": "sm",
                                                                "weight": "regular"
                                                              },
                                                              {
                                                                "type": "text",
                                                                "text": "' . $rs['displayname_th'] . '",
                                                                "size": "sm",
                                                                "align": "end",
                                                                "color": "#AAAAAA"
                                                              }
                                                            ]
                                                          },
                                                          {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                              {
                                                                "type": "text",
                                                                "text": "Name Eng",
                                                                "flex": 0,
                                                                "margin": "sm",
                                                                "weight": "regular"
                                                              },
                                                              {
                                                                "type": "text",
                                                                "text": "' . $rs['displayname_en'] . '",
                                                                "size": "sm",
                                                                "align": "end",
                                                                "color": "#AAAAAA"
                                                              }
                                                            ]
                                                          },
                                                          {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                              {
                                                                "type": "text",
                                                                "text": "Type",
                                                                "flex": 0,
                                                                "margin": "sm",
                                                                "weight": "regular"
                                                              },
                                                              {
                                                                "type": "text",
                                                                "text": "' . $rs['employee_type'] . '",
                                                                "size": "sm",
                                                                "align": "end",
                                                                "color": "#AAAAAA"
                                                              }
                                                            ]
                                                          },
                                                          {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                              {
                                                                "type": "text",
                                                                "text": "Organization",
                                                                "flex": 0,
                                                                "margin": "sm",
                                                                "weight": "regular"
                                                              },
                                                              {
                                                                "type": "text",
                                                                "text": "' . $rs['organization'] . '",
                                                                "size": "sm",
                                                                "align": "end",
                                                                "color": "#AAAAAA"
                                                              }
                                                            ]
                                                          },
                                                          {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                              {
                                                                "type": "text",
                                                                "text": "department",
                                                                "flex": 0,
                                                                "margin": "sm",
                                                                "weight": "regular"
                                                              },
                                                              {
                                                                "type": "text",
                                                                "text": "' . $rs['department'] . '",
                                                                "size": "sm",
                                                                "align": "end",
                                                                "color": "#AAAAAA"
                                                              }
                                                            ]
                                                          }
                                                        ]
                                                      },
                                                      {
                                                        "type": "separator"
                                                      },
                                                      {
                                                        "type": "text",
                                                        "text": "TU APIs Developers",
                                                        "size": "xxs",
                                                        "color": "#AAAAAA",
                                                        "wrap": true
                                                      }
                                                    ]
                                                  },
                                                  "footer": {
                                                    "type": "box",
                                                    "layout": "vertical",
                                                    "contents": [
                                                      {
                                                        "type": "button",
                                                        "action": {
                                                          "type": "uri",
                                                          "label": "Website",
                                                          "uri": "https://restapi.tu.ac.th"
                                                        },
                                                        "margin": "xs",
                                                        "style": "primary",
                                                        "gravity": "top"
                                                      }
                                                    ]
                                                  }
                                                }
                                              }';
            $messages .= '

                        ]
                      }
                      ';
        }
    }
    return $messages;
}

function get_data_std($stdid = NULL, $replyToken = NULL) {
    if (!empty($stdid)and ! empty($replyToken)) {
        $url = 'https://restapi.tu.ac.th/api/v1/profile/std/info/?id=' . $stdid;
        $headers = array('Content-Type: application/json', 'Application-Key: {Token Key From TU APIs}');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result);
        if (!empty($data)and ! empty($data->displayname_th)) {


            $rs['studentid'] = $data->studentid;
            $rs['displayname_th'] = $data->displayname_th;
            $rs['displayname_en'] = $data->displayname_en;
            $rs['birthdate'] = $data->birthdate;
            $rs['statsid'] = $data->statsid;
            $rs['statusname'] = $data->statusname;
            $rs['level_name'] = $data->level_name;
            $rs['faculty'] = $data->faculty;
            $rs['department'] = $data->department;



            $messages = '
                {
                "replyToken":"' . $replyToken . '",
                "messages":[
                ';
            $messages .= '
        
                   {
                        "type": "flex",
                        "altText": "Flex Message",
                        "contents": {
                          "type": "bubble",
                          "body": {
                            "type": "box",
                            "layout": "vertical",
                            "spacing": "md",
                            "action": {
                              "type": "uri",
                              "label": "Action",
                              "uri": "https://linecorp.com"
                            },
                            "contents": [
                              {
                                "type": "text",
                                "text": "Getting Student Info",
                                "size": "md",
                                "weight": "bold"
                              },
                              {
                                "type": "separator"
                              },
                              {
                                "type": "box",
                                "layout": "vertical",
                                "spacing": "sm",
                                "contents": [
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "ID",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['studentid'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Name Thai",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['displayname_th'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Name Eng",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['displayname_en'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Type",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['birthdate'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Status ID",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['statsid'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Status Name",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['statusname'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Level",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['level_name'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Faculty",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['faculty'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  },
                                  {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                      {
                                        "type": "text",
                                        "text": "Department",
                                        "flex": 0,
                                        "margin": "sm",
                                        "weight": "regular"
                                      },
                                      {
                                        "type": "text",
                                        "text": "' . $rs['department'] . '",
                                        "size": "sm",
                                        "align": "end",
                                        "color": "#AAAAAA"
                                      }
                                    ]
                                  }
                                ]
                              },
                              {
                                "type": "separator"
                              },
                              {
                                "type": "text",
                                "text": "TU APIs Developers",
                                "size": "xxs",
                                "color": "#AAAAAA",
                                "wrap": true
                              }
                            ]
                          },
                          "footer": {
                            "type": "box",
                            "layout": "vertical",
                            "contents": [
                              {
                                "type": "button",
                                "action": {
                                  "type": "uri",
                                  "label": "Website",
                                  "uri": "https://restapi.tu.ac.th"
                                },
                                "margin": "xs",
                                "style": "primary",
                                "gravity": "top"
                              }
                            ]
                          }
                        }
                      }
                      

                      ';
            $messages .= '

                        ]
                      }
                      ';
        }
    }

    return $messages;
}

echo "Hello TU APIs Developers";
