New message from the {{ $site }} contact form.

Name: {!! $data['name'] !!}
Email: {!! $data['email'] !!}
Organisation: {!! $data['org'] ?? '' !!}
Topic: {!! $data['topic'] !!}
Site: {{ $site }}

Message:
{!! $data['message'] ?? '' !!}
