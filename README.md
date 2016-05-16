# Office Health Monitor - PHP Server

A simple PHP server-app to store and provide metrics data by/for IoT meteorological stations

## Requirements

- PHP/Apache
- MySQL

## API

### GET requests

Use GET to query metrics data from the database. 

- A blank request with no parameters will return **all** records from **all** devices.
- The `positionId` parameter filters records from a specific device ID.
- Filtering by time is possible by using the `gtetime` (>=) and `lttime` (<) parameters. These apply only when `positionId` is also used.

The expected response is a JSON string:

    {
        "message" : "Get metrics was successful",
        "query" : "SELECT * FROM `metrics` WHERE `positionId` = 1 ORDER BY `time` DESC",
        "result" : [
            {
                "metricId" : "10",
                "positionId" : "1",
                "time" : "1463306783",
                "temp" : "1",
                "wet" : "1",
                "gas" : "1",
                "light" : "1",
                "noise" : "1"
            },
            {
                "metricId" : "9",
                "positionId" : "1",
                "time" : "1461619154",
                "temp" : "22",
                "wet" : "35",
                "gas" : "1",
                "light" : "80",
                "noise" : "20"
            },
            ...  
        ]
    }

### POST requests

Use POST to record/insert metrics data. The available POST parameters are:

- `positionId` - (REQUIRED) Numeric ID of the device 
- `temp` - (optional) Temperature
- `wet` - (optional) Moisture
- `gas` - (optional) CO2
- `light` - (optional) Illuminance
- `noise` - (optional) Background noise

The expected response is a JSON string:

    {
        "message" : "Post was successful"
    }

## Configuration

The server-app will look for a `config.ini` file in the current directory. It should contain at least the database credentials in the following form:

    [database]
    database=***
    host=***
    user=***
    password=***

You will need to create a database and the table that the server-app will need. You can use the query below to create the table:

    CREATE TABLE IF NOT EXISTS `metrics` (
      `metricId` int(8) NOT NULL AUTO_INCREMENT,
      `positionId` int(8) NOT NULL,
      `time` int(8) NOT NULL,
      `temp` int(4) DEFAULT NULL,
      `wet` int(4) DEFAULT NULL,
      `gas` int(4) DEFAULT NULL,
      `light` int(4) DEFAULT NULL,
      `noise` int(4) DEFAULT NULL,
      PRIMARY KEY (`metricId`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
