# API Documentation
## Purpose
I made an api to get music from my server. This API is public so feel free to try at [api.musiques.nils.test.sc2mnrf0802.universe.wf](http://api.musiques.nils.test.sc2mnrf0802.universe.wf/?file=An+Oasis+In+Time)    

## URIs  
- __[music-infos *.php*](music-infos.php)__: Gives the JSON or XML format of the requested ressource. 

   
- __[mp3 *.php*](mp3.php)__: Gives the mp3 file of the requested ressource, request other type (like .wav) will send an error.  


- __[html *.php*](html.php)__: Gives the HTML format of the requested ressource.    


- __[list *.php*](list.php)__: Gives the list of available musics


- __[index *.php*](index.php)__: Redirects to [music-infos](music-infos.php); will **lose the headers** so it is not recomended to requests this page instead of `get-req`


## How to make a request
### Quick explaination
In order to make a request you have to send a HTTP request to one of the endpoints with the parameter "__`file`__" set to the name of the music you want, the extention (.mp3) is optional.  
Ex: `curl api.musiques.nils.test.sc2mnrf0802.universe.wf/music-infos?file=An%20Oasis%20In%20Time`

### Typical request
Here is the request shown in the last part: 
```
GET /music-infos?file=An%20Oasis%20In%20Time HTTP/1.0
X-Country-Code: FR
X-Autonomous-System: 15557
Host: api.musiques.nils.test.sc2mnrf0802.universe.wf
X-Forwarded-Proto: http
User-Agent: curl/8.4.0
Accept: */*
Content-Length: 0
```
This request will send back the body:
```
{
    "title":  "An oasis in time",
    "composers": [
        "Michiru Yamane"
    ],
    "track": 39,
    "commentaire": "",
    "path": "An Oasis In Time.mp3"
}
```
If the header `Accept` is set to `application/xml` the body will look like this:
```
<music>
    <title>An oasis in time</title>
    <composers>
        <composer>Michiru Yamane</composer>
    </composers>
    <track>39</track>
    <album>Skullgirls</album>
    <commentaire></commentaire>
    <path>An Oasis In Time.mp3</path>
</music>
```

A succeful request will always respond with those 5 fields
+ _String_ __`title`__ : The full title of the music, often the file name.
+ _String[]_ __`composers`__: The composers of the music.
+ _Unsigned Int_ __`track`__: The number of the track.
+ _String_ __`commentaire`__: A comment I writed, often empty.
+ _String_ __`path`__: The path of the file on the server (just the basename of the music).

### Particular URIs:
+ __[html](html.php)__: You can set a boolean parameter `title` to _true_ to get an intro. 
You can get a response:
```
<div class='music-head'>
    <p><span class='music-title'>An oasis in time</span> (<span class='music-artists'>Michiru Yamane</span> | <span class='music-track'> 39 </span> | music in album <span class='music-album'>Skullgirls</span>)
</div>
<audio src='http://musiques.nils.test.sc2mnrf0802.universe.wf/api/An Oasis In Time.mp3' type='audio/mp3' controls autoplay></audio>%
<audio src="http://musiques.nils.test.sc2mnrf0802.universe.wf/api/An Oasis In Time.mp3" type="audio/mp3" controls="" autoplay=""></audio>
```

The `p` element has the class `music-head`, the title `music-title`, the artists `music-artists`, ...


+ __[music-infos](music-infos.php)__: You can inculde the `indent=`_`n`_ parameter to get the request with _`n`_ indent, the returned indent is in the header `Body-Indent`.

+ __[list](list.php)__: This endpoint will send back a list of responses so you will get somthing like this:
```
[
    {
        "title":  "Resurrections",
        "composers": [
            "Lena Raine"
        ],
        "track": 3,
        "album": "Celeste",
        "commentaire": "",
        "path": "03 - Resurrections.mp3"
    },
    {
        "title":  "Awake",
        "composers": [
            "Lena Raine"
        ],
        "track": 4,
        "album": "Celeste",
        "commentaire": "",
        "path": "04-Awake.mp3"
    }
]
```
or
```
<musics>
    <music>
        <title>Resurrections</title>
        <composers>
            <composer>Lena Raine</composer>
        </composers>
        <track>3</track>
        <album>Celeste</album>
        <commentaire></commentaire>
        <path>03 - Resurrections.mp3</path>
    </music>
    <music>
        <title>Awake</title>
        <composers>
            <composer>Lena Raine</composer>
        </composers>
        <track>4</track>
        <album>Celeste</album>
        <commentaire></commentaire>
        <path>04-Awake.mp3</path>
    </music>
</music>
```

## Handled Headers

- `Accept`: The MIME types expected separated by a comma(`,`), possibility to give the weight argument (`q`). ex: `Accept: application/json, */*; q=0.7`. 
    ### Accepted types: 
    + application/json (default),
    + application/xml,
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
+ __`stack_trace`__: only useful on debug (only availble for testers and admin)
+ __`other_info`__: complementary infos on the reason of the failure.

ex:  
```
{
    "code": 404,
    "name": "Not found",
    "message": "The specified element does not exist."
    "stack-trace": "#0 /home/sc2mnrf0802/api.musique.nils.test/music-infos.php(34): checkParam() #1 {main}",
    "other_info": "Path: http://musiques.nils.test.sc2mnrf0802.universe.wf/api/example.mp3"  
}
```
