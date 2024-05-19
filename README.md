# API Documentation
## Purpose
I made an api to get music from my server. This API is public so feel free to try at [api.musiques.nils.test.sc2mnrf0802.universe.wf](http://api.musiques.nils.test.sc2mnrf0802.universe.wf/?file=An+Oasis+In+Time)  (I also created a shorter url: [bit.ly/API_nils_test](https://bit.ly/API_nils_test))

## URI list
- __[music-infos *.php*](music-infos.php)__: Gives the ressource's headers (title, n° of track, etc...) in JSON or XML. 

   
- __[mp3 *.php*](mp3.php)__: Gives the audio (in mp3) of the requested ressource, requesting an other type (like .wav) will send an error.  


- __[html *.php*](html.php)__: Equivalent to [music-infos](music-infos.php) but with a differnent style and in a HTML format.    


- __[list *.php*](list.php)__: Gives the list of all available musics


- __[index *.php*](index.php)__: Redirects to [music-infos](music-infos.php).


## How to make a request
### Quick explaination
In order to make a request, you have to send a HTTP request to one of the endpoints with the parameter "`file`" set to the name of the music you want, the extention (.mp3) is optional.  
Ex: `curl api.musiques.nils.test.sc2mnrf0802.universe.wf/music-infos?file=An%20Oasis%20In%20Time`

### Typical request
Here is the request shown in the last part: 
```
GET /music-infos?file=An%20Oasis%20In%20Time HTTP/1.0
X-Country-Code: FR
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
If the header `Accept` is set to `application/xml` the body will be:
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

A succeful request will send a response with those 5 fields
+ _String_ __`title`__ : The full title of the music, often the file's name.
+ _String[]_ __`composers`__: The composers of the music.
+ _Unsigned Int_ __`track`__: The number of the track.
+ _String_ __`commentaire`__: A comment I wrote, often empty.
+ _String_ __`path`__: The path of the file on the server starting in the `api` folder (just the basename of the music).

### Request with browser
With the new style compatibility, you can now request in browser. Make sure to have the _redirect_ param disabled in order to not get redirected to the __[html](html.php)__ page. 

### Particular URIs:
+ __[html](html.php)__: You can set a boolean parameter `title` to _true_ to get some metadatas.
You can get a response:
```
<div class='music-head'>
    <p><span class='music-title'>An oasis in time</span> (<span class='music-artists'>Michiru Yamane</span> | <span class='music-track'> 39 </span> | music in album <span class='music-album'>Skullgirls</span>)
</div>
<audio src="http://musiques.nils.test.sc2mnrf0802.universe.wf/api/An Oasis In Time.mp3" type="audio/mp3" controls="" autoplay=""></audio>
```

The `div` element has the class `music-head`, the title `music-title`, the artists `music-artists`, ...


+ __[music-infos](music-infos.php)__: You can include the `indent=` _`n`_ parameter to get the request with _`n`_ indent (the `\t` char), the returned indent is in the header `Body-Indent`.

+ __[list](list.php)__: This endpoint will send back a list of responses from [music-infos](music-infos.php), here is a short example:
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

### Particular parameters in URL
+ _bool_ `redirect`: Define if redirections should be done works for [music-infos](music-infos.php), [mp3](mp3.php) and [html](html.php) endpoints.
+ _bool_ `styled`: Equivalent to the `Body-Style` header, controls whether or not the response should have a predefined stylesheet attached. Works for [music-infos](music-infos.php) and [list](list.php).

#### Write a boolean parameter
There's 4 accepted values:
+ `true` <=> `1`
+ `false` <=> `0`

## Handled Headers

- __`Accept`__: The MIME types expected separated by a comma(`,`), you can include the weight argument (`q`). ex: `Accept: application/json, */*; q=0.7`. By convention, the weight should be between 0 and 1, however any positive value is correct.
    ### Accepted types: 
    + **application/json** (default),
    + application/xml,
    + audio/mp3,
    + text/html
- __`Accept-Charset`__: The accepted charset. Can only provide __utf-8__.
- __`Accept-Language`__: The accepted language. Can only provide __english__.
- __`Accept-Error`__: The type to send back if an error occurs, will override the `Accept` header, takes only:
    + **application/json** (default),
    + application/xml
### particular headers
+ _bool_ **`Body-Style`**: If the response should link a predefined stylesheet, specific to the [music-infos](music-infos.php) endpoint with an XML response.

## Handled methods
For now, any other method that __`GET`__, __`POST`__ or __`HEAD`__ will send back an error message.  
A `GET` request is equivalent to a `POST` request (parsed as an HTML form), the server will send the headers of the music. A `HEAD` request will send back only the headers.

## Handling errors
The server will __always__ send the error message in JSON.  
The JSON will always have 5 fields:
+ __`code`__: The HTTP error code or 0, if 0 the error is not intended.
+ __`name`__: The name of the HTTP error __or__ if code == 0 the name of the class of the error.
+ __`message`__: A message explaining the error
+ __`stack_trace`__: the stack trace (only availble for testers and admin)
+ __`other_info`__: further informations on the reason of the failure.

ex:  
```
{
    "code": 404,
    "name": "Not found",
    "message": "The specified element does not exist."
    "other_info": "Path: http://musiques.nils.test.sc2mnrf0802.universe.wf/api/example.mp3"  
}
```
