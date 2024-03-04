# API Documentation
## Purpose
I made an api to get music from my server. This API is public so feel free to try at <api.musiques.nils.test.sc2mnrf0802.universe.wf>   

## Endpoints  
- `get-req.php`: Give the JSON format of the requested ressource. 

   
- `get-music.php`: Give the mp3 file of the requested ressource, request other type (like .wav) will send an error.  


- `get-html.php`: Give the HTML format of the requested ressource.    


- `index.php`: Redirects to `get-req.php`; will **lose the headers** so it is not recomended to requests this page instead of `get-req`


## How to make a request
In order to make a request you have to send a HTTP request to one of the endpoints with the parameter "__`file`__" set to the name of the music you want, the extention (.mp3) is optional.  
Ex: `curl api.musiques.nils.test.sc2mnrf0802.universe.wf/get-req.php?file=An%20Oasis%20In%20Time`

## Handled Headers

- `Accept`: The MIME types expected separated by a comma(`,`), possibility to give the weight argument (`q`). ex: `Accept: application/json, */*; q=0.7`. 
    ### Accepted types: 
    + application/json (default),
    + audio/mp3,
    + text/html
- `Accept-Charset`: The accepted charset. Can only provide __utf-8__.
- `Accept-Language`: The accepted language. Can only provide __english__.

## Handled methods
For now, any other method that __`GET`__, __`POST`__ or __`HEAD`__ will send back an error message.  
A `GET` request is equivalent to a `POST` request, the server will send the headers of the music. A `HEAD` request will send back only the headers.

## Handling errors
The server will __always__ send the error message in JSON.  
The JSON will always have 5 fields:
+ __`code`__: The HTTP error code or 0, if 0 the error is not intended.
+ __`name`__: The name of the HTTP error __or__ if code == 0 the name of the class of the error.
+ __`message`__: A message explaining the error
+ __`stack_trace`__: only useful on debug
+ __`other_info`__: complementary infos on the reason of the failure.

ex:  
```
{
    "code": 404,
    "name": "Not found",
    "message": "The specified element does not exist."
    "stack-trace": "#0 /home/sc2mnrf0802/api.musique.nils.test/get-req.php(34): checkParam() #1 {main}",
    "other_info": "Path: http://musiques.nils.test.sc2mnrf0802.universe.wf/api/example.mp3"  
}
```

## Musics available
I am working on a list endpoint that could list the musics availble but for now I have a list.
- 03 - Resurrection
- 04 - Awake
- 07 - Spirit of Hospitality
- 08 - Pale Court
- 08 - Scattered and Lost
- 09 - Gods & Glory
- A Return To Normalcy
- An Oasis In Time
- An Oasis Of Blood
- Ancient Ruins Night
- Ancient Ruins Relaxed
- Ancient Ruins Theme
- Climatic Battle
- Crypt (Combat)
- Desert Firestorm
- Dirge of the Divine Trinity
- Fugue In Three Goddesses
- Gerudo Colosseum (Combat)
- Indigo Quarry Night
- Indigo Quarry Relaxed
- Indigo Quarry Theme
- Let's Dance Boys!
- Mysterious Destiny
- One Of A Kind
- Paved With Good Intentions
- Radio Romantic
- Red & Black
- Riders Of The Light
- ST01 墜落する軍用輸送機
- ST02 ヴィグリッド 駅ホーム
- ST03 ヴィグリッド 市街地
- ST08 パラディソ - 時の記憶の墓場
- The Gates Of Hell
- Unfinished Business
- 友よ