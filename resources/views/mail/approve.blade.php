@extends('layouts.layout')
@section('email')
    <tr>
        <td style="padding:0 0 36px 0;color:#153643;">
            <h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;">Chúng tôi rất tiếc phải thông báo :
                {{ $otp }}</h1>
            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Lorem ipsum dolor sit
                amet, consectetur adipiscing elit. In tempus adipiscing felis, sit amet blandit ipsum volutpat sed. Morbi
                porttitor, eget accumsan et dictum, nisi libero ultricies ipsum, posuere neque at erat.</p>
            <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><a href="https://nal.vn/vi/"
                                                                                                 style="color:#ee4c50;text-decoration:underline;">https://nal.vn/vi/</a></p>
        </td>
    </tr>
@endsection
