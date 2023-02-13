# simple webhook script for naver pinpoint apm
[Pinpoint APM](https://github.com/pinpoint-apm/pinpoint)

# Usage

```
# build
docker build -t pinpoint-webhook .

# run
docker run --network pinpoint-docker_pinpoint -d -p 80:80 pinpoint-webhook


# test 
curl -H 'Content-Type: application/json' <webhook_listening_address> \
-d '{
    "pinpointUrl": "<pinpointUrl>",
    "batchEnv": "release",
    "applicationId": "<applicationId>",
    "serviceType": "SPRING_BOOT",
    "checker": {
      "name": "TOTAL COUNT",
      "type": "LongValueAlarmChecker",
      "detectedValue": 42
    },
    "unit": "",
    "threshold": 1,
    "notes": "my first hook",
    "sequenceCount": 1
  }'

```

