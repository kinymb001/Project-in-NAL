<!DOCTYPE html>
<html>
<head>
    <title>Revision Status Notification</title>
</head>
<body>
<h1>Revision Status Notification</h1>
<p>Hello {{ $revision->user->name }},</p>
<p>Your revision for article with ID {{ $revision->article_id }} has been {{ $status }}.</p>
@if ($status === 'rejected')
    <p>Reason for rejection: {{ $reason }}</p>
@endif
<p>Thank you for your contribution.</p>
</body>
</html>
