<head>
    <style>
    * {
        margin: 0;
        padding: 0;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p {
        font-family: sans-serif !important;
        color: #3D3D3D !important;
    }

    * {
        font-family: sans-serif !important;
    }

    img {
        max-width: 100%;
    }

    .collapse {
        margin: 0;
        padding: 0;
    }

    body {
        -webkit-font-smoothing: antialiased;
        -webkit-text-size-adjust: none;
        width: 100% !important;
        height: 100%;
    }


    /* ------------------------------------- 
        ELEMENTS 
------------------------------------- */

    /*a {
        color: #2BA6CB;
    }*/

    .btn {
        text-decoration: none;
        color: #FFF !important;
        background-color: #16baff;
        padding: 2px 25px;
        font-weight: bold;
        margin-right: 10px;
        text-align: center;
        cursor: pointer;
        display: inline-block;
    }

    p.callout {
        padding: 15px;
        background-color: #ECF8FF;
        margin-bottom: 15px;
    }

   /* .callout a {
        font-weight: bold;
        color: #2BA6CB;
    }*/

    table.social {
        /*  padding:15px; */
        background-color: #ebebeb;

    }

    .social .soc-btn {
        padding: 3px 7px;
        font-size: 12px;
        margin-bottom: 10px;
        text-decoration: none;
        color: #FFF;
        font-weight: bold;
        display: block;
        text-align: center;
    }

   /* a.fb {
        background-color: #3B5998 !important;
    }

    a.tw {
        background-color: #1daced !important;
    }

    a.gp {
        background-color: #DB4A39 !important;
    }

    a.ms {
        background-color: #000 !important;
    }*/

    .sidebar .soc-btn {
        display: block;
        width: 100%;
    }

    /* ------------------------------------- 
        HEADER 
------------------------------------- */

    table.head-wrap {
        width: 100%;
    }

    .header.container table td.logo {
        padding: 15px;
    }

    .header.container table td.label {
        padding: 15px;
        padding-left: 0px;
    }


    /* ------------------------------------- 
        BODY 
------------------------------------- */

    table.body-wrap {
        width: 100%;
    }

    h3.color {
        color: #3D3D3D !important;
    }

    head .span-font {
        font-size: 17px;
        font-weight: 100;
    }

    /* ------------------------------------- 
        FOOTER 
------------------------------------- */

    table.footer-wrap {
        width: 100%;
        clear: both !important;
    }

    .footer-wrap .container td.content p {
        border-top: 1px solid rgb(215, 215, 215);
        padding-top: 15px;
    }

    .footer-wrap .container td.content p {
        font-size: 10px;
        font-weight: bold;

    }


    /* ------------------------------------- 
        TYPOGRAPHY 
------------------------------------- */

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: sans-serif !important;
        color: #3D3D3D !important;
    }

    h1 small,
    h2 small,
    h3 small,
    h4 small,
    h5 small,
    h6 small {
        font-size: 60%;
        color: #3D3D3D;
        line-height: 0;
        text-transform: none;
    }

    h1 {
        font-weight: 200;
        font-size: 44px;
    }

    h2 {
        font-weight: 200;
        font-size: 37px;
    }

    h3 {
        font-weight: 500;
        font-size: 27px;
    }

    h4 {
        font-weight: 500;
        font-size: 23px;
    }

    h5 {
        font-weight: 900;
        font-size: 17px;
    }

    h6 {
        font-weight: 900;
        font-size: 14px;
        text-transform: uppercase;
        color: #3D3D3D;
    }

    .collapse {
        margin: 0 !important;
    }

    p,
    ul {
        margin-bottom: 10px;
        font-weight: normal;
        font-size: 14px;
        line-height: 1.6;
    }

    p.lead {
        font-size: 17px;
            text-align: justify;
    }

    p.last {
        margin-bottom: 0px;
    }

    ul li {
        margin-left: 5px;
        list-style-position: inside;
    }



    /* --------------------------------------------------- 
        RESPONSIVENESS
        Nuke it from orbit. It's the only way to be sure. 
------------------------------------------------------ */

    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */

    .container {
        display: block !important;
        max-width: 600px !important;
        /*margin: 0 auto !important;*/
        /* makes it centered */
        clear: both !important;
    }

    /* This should also be a block element, so that it will fill 100% of the .container */

    .content {
        padding: 15px;
        max-width: 600px;
        /*margin: 0 auto;*/
        display: block;
    }

    /* Let's make sure tables in the content area are 100% wide */

    .content table {
        width: 100%;
    }


    /* Odds and ends */

    .column {
        width: 300px;
        float: left;
    }

    .column tr td {
        padding: 15px;
    }

    .column-wrap {
        padding: 0 !important;
        margin: 0 auto;
        max-width: 600px !important;
    }

    .column table {
        width: 100%;
    }

    .social .column {
        width: 280px;
        min-width: 279px;
        float: left;
    }

    /* Be sure to place a .clear element after each set of columns, just to be safe */

    .clear {
        display: block;
        clear: both;
    }


    /* ------------------------------------------- 
        PHONE
        For clients that support media queries.
        Nothing fancy. 
-------------------------------------------- */

    @media only screen and (max-width: 600px) {

        a[class="btn"] {
            display: block !important;
            margin-bottom: 10px !important;
            background-image: none !important;
            margin-right: 0 !important;
        }

        div[class="column"] {
            width: auto !important;
            float: none !important;
        }

        table.social div[class="column"] {
            width: auto !important;
        }

    }
    .newClass{font-size: .875rem !important;
   letter-spacing: .25px !important;
   border-radius: 4px !important;
   box-sizing: border-box !important;
   color: #ffffff !important;
   cursor: pointer !important;
   font-weight: 600 !important;
   background-color: #19baff;
   height: 36px !important;
   padding: 8px 33px !important;
   text-align: center !important;
   box-shadow: inset 0 0 0 1px #dadce0 !important;
   user-select: none !important;
   text-decoration: none !important;
    }
    .newClass:hover{
    background-color: #47c6fb !important;
    color:#ffffff !important;
    }
    .img-size{
width:100% !important;
height:350px;
}

 @media only screen and (max-width: 1180px) {
 .img-size{
      width:100% !important;
      height:300px !important;
}
 }
  @media only screen and (max-width: 992px) {
 .img-size{
      width:100% !important;
      height:250px !important;
}
 }
 
   @media only screen and (max-width: 899px) {
    .img-size{
      width:100% !important;
      height:230px !important;
}
 }
</style>
</head>

<body bgcolor="#FFFFFF">

    <div class="row" style="background-color:#19baff">
        <center>
            <img src="https://www.thewavesapp.online/assets/images/emailHeader.png"
                                    class="img-size" />
        </center>

    </div>
    {{-- <!-- HEADER -->
    <table class="head-wrap" bgcolor="#f7f7f7">
        <tr>
            <td></td>
            <td class="header container">

                <div class="content">
                    <table bgcolor="#f7f7f7">
                        <tr>

                            <td>
                                <img src="{{ url('/public/img/logo.png') }}"
                                width="200px" height="100px" />
                            </td>

                        </tr>
                    </table>
                </div>

            </td>
            <td></td>
        </tr>
    </table>
    <!-- /HEADER --> --}}


    <!-- BODY -->
{{--     <table class="body-wrap">
        <tr>
            <td></td> --}}
            {{-- <td class="container" bgcolor="#FFFFFF"> --}}

                {{-- <div class="content"> --}}
                    {{-- <table>
                        <tr> --}}
                            {{-- <td> --}}
{{--                                 <p class="lead">We are excited to have you join our community. Here’s what you need to know.
                                </p>
                                <br>
                                <hr>
                                <br>

                                <p class="lead">Hello <b>{{ $name }}</b>! --}}
                                <p class="lead"><b>Hi {{ $name }},</b>

                                </p>

                                <p class="lead">You’ve been added as a student to your school’s Waves Network and have been given access to the Waves App. Having access to the Waves App which shortens the distance between students and supports services offered by your school. Whether you need to speak with your counselor, find a local resource, connect with crisis support, or anything in between - this app has is covered. To access the Waves App, you will log in using the following information:
                                </p>

                            {{--     <p class="lead">Before jumping into the Waves App, here are a few things to know. To access the Waves App,
                                    you will log in using the following information: </p> --}}

                                    <p class="lead"><b>Student ID:</b> {{ $studentId }} </p>
                                    <p class="lead"><b>School Access Code:</b> {{ $accessCode }} </p>
                                    <p class="lead"><b>School Authorization Code:</b> {{ $authCode }}</p>


                                <p class="lead">Once you select your school and connect to your school’s Waves Network, you will be required to create
a password to protect your account. Once you knock out these initial few steps, you’re good to go. If
you’re feeling ready, download the app below to set up your account. We’re glad you’re here.</p>

{{--                                 <p class="lead">Once you set up your account, you will have direct access to communication and scheduling
                                    tools with your school’s office of student services, local resources, crisis support
                                    resources, and more. </p> --}}

{{--                                 <p class="lead">If you’re feeling ready, click below and jump in. We’re glad you’re here. </p> --}}


                                <p class="lead">Download the Waves App <a href="https://itunes.apple.com/us/app/the-waves-app/id1398442546?ls=1&mt=8">(IOS)</a> <a href="https://play.google.com/store/apps/details?id=com.abovethewaves">(Android)</a> <br> Or <a 
                                    target="_blank" href="{{ url('../signup') }}">click here </a>to log in online.</p>

                                {{-- <a class="newClass"
                                target="_blank" href="{{ url('../waves/#/signup') }}">Download the App (IOS)</a>
                                &nbsp;&nbsp;
                                <a class="newClass"
                                target="_blank" href="{{ url('../waves/#/signup') }}">Download the App (Android)</a> --}}

                                {{-- <p class="lead">Or click <a 
                                    target="_blank" href="{{ url('../waves/#/signup') }}">here </a>to log in online.</p> --}}


                                <p class="lead">Stay Awesome,<br>The Waves Team<br><u>hello@abovethewaves.co</u></p>
                                <br>
                                <br>

                            {{-- </td> --}}
                        {{-- </tr>
                    </table> --}}
                {{-- </div> --}}
{{-- 
            </td>
            <td></td>
        </tr>
    </table> --}}
    <!-- /BODY -->


    <div class="row" style="background-color:#f7f7f7">
    <center><br>
            Copyright© 2019 Above the Waves, Inc, All rights reserved.
            <br>
            <br>
    </center>

    </div>
    {{-- <table class="head-wrap" bgcolor="#f7f7f7">
        <tr>
            <td></td>
            <td class="header container">

                <div class="content">
                    <table bgcolor="#f7f7f7">
                        <tr>
                            <td align="center">

                                <img src="{{ url('/public/img/logo.png') }}"
                                width="150px" height="80px" />


                            </td>

                        </tr>
                        <tr>
                            <td align="center">
                                <p>
                                    © 2018 Above the Waves, Inc.
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
            <td></td>
        </tr>
    </table> --}}
    <!-- /HEADER -->

</body>