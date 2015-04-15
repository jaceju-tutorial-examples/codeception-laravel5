<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player</title>
</head>
<body>
{!! Form::open(['method' => 'get']) !!}
    <label for="search">Search:</label>
    <input type="text" name="q" id="search" value="{{ @$keyword }}"/>
    <input type="submit" value="Search"/>
{!! Form::close() !!}

@if (!empty($keyword))
<p>沒有找到任何歌曲</p>
@endif
</body>
</html>
