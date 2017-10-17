<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>500 - 服务器错误</title>
</head>
<body>
<div style="padding: 50px 100px;">
    <div style="font-size: 120px;">:(</div>
    <div style="font-size: 32px;"><?=get_class($result)?> : <?=$result->getMessage()?></div>
    <div style="font-family: Consolas, Menlo, 'Courier New', Monaco, monospace; font-size: 16px; color: #666;">
        <br><?=str_replace("\n", '<br>', $result->getTraceAsString())?><br><br>
    </div>
    <hr>
    <div style="color: #999;">Powered by BestLang</div>
</div>
</body>
</html>