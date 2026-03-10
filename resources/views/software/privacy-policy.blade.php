@extends('software.layout.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h1 class="h3 mb-3">Privacy Policy</h1>
                    <p><strong>Last updated on Sep 8th 2024</strong></p>

                    <p>
                        This privacy policy sets out how <strong>Vrundavan Computers</strong> uses and protects any information
                        that you give Vrundavan Computers when you visit their website and/or agree to purchase from them.
                    </p>

                    <p>
                        Vrundavan Computers is committed to ensuring that your privacy is protected. Should we ask you to provide
                        certain information by which you can be identified when using this website, then you can be assured that
                        it will only be used in accordance with this privacy statement.
                    </p>

                    <p>
                        Vrundavan Computers may change this policy from time to time by updating this page. You should check
                        this page from time to time to ensure that you adhere to these changes.
                    </p>

                    <h2 class="h5 mt-4">Information We May Collect</h2>
                    <ul>
                        <li>Name</li>
                        <li>Contact information including email address</li>
                        <li>Demographic information such as postcode, preferences and interests</li>
                        <li>Other information relevant to customer surveys and/or offers</li>
                    </ul>

                    <h2 class="h5 mt-4">What We Do With The Information We Gather</h2>
                    <p>
                        We require this information to understand your needs and provide you with a better service,
                        and in particular for the following reasons:
                    </p>
                    <ul>
                        <li>Internal record keeping.</li>
                        <li>We may use the information to improve our products and services.</li>
                        <li>
                            We may periodically send promotional emails about new products, special offers or other
                            information which we think you may find interesting using the email address you have provided.
                        </li>
                        <li>
                            From time to time, we may also use your information to contact you for market research purposes.
                            We may contact you by email, phone, fax or mail.
                        </li>
                        <li>
                            We may use the information to customise the website according to your interests.
                        </li>
                    </ul>

                    <p>
                        We are committed to ensuring that your information is secure. In order to prevent unauthorised
                        access or disclosure we have put in suitable measures.
                    </p>

                    <h2 class="h5 mt-4">How We Use Cookies</h2>
                    <p>
                        A cookie is a small file which asks permission to be placed on your computer's hard drive.
                        Once you agree, the file is added and the cookie helps analyze web traffic or lets you know
                        when you visit a particular site.
                    </p>

                    <p>
                        Cookies allow web applications to respond to you as an individual. The web application can
                        tailor its operations to your needs, likes and dislikes by gathering and remembering
                        information about your preferences.
                    </p>

                    <p>
                        We use traffic log cookies to identify which pages are being used. This helps us analyze data
                        about webpage traffic and improve our website in order to tailor it to customer needs.
                        We only use this information for statistical analysis purposes and then the data is removed
                        from the system.
                    </p>

                    <p>
                        Overall, cookies help us provide you with a better website by enabling us to monitor which
                        pages you find useful and which you do not. A cookie in no way gives us access to your computer
                        or any information about you other than the data you choose to share with us.
                    </p>

                    <p>
                        You can choose to accept or decline cookies. Most web browsers automatically accept cookies,
                        but you can usually modify your browser setting to decline cookies if you prefer. This may
                        prevent you from taking full advantage of the website.
                    </p>

                    <h2 class="h5 mt-4">Controlling Your Personal Information</h2>
                    <p>
                        You may choose to restrict the collection or use of your personal information in the following ways:
                    </p>

                    <ul>
                        <li>
                            Whenever you are asked to fill in a form on the website, look for the box that you can
                            click to indicate that you do not want the information to be used by anybody for direct
                            marketing purposes.
                        </li>
                        <li>
                            If you have previously agreed to us using your personal information for direct marketing
                            purposes, you may change your mind at any time by writing to or emailing us.
                        </li>
                    </ul>

                    <p>
                        We will not sell, distribute or lease your personal information to third parties unless we
                        have your permission or are required by law to do so.
                    </p>

                    <p>
                        If you believe that any information we are holding on you is incorrect or incomplete,
                        please write to:
                    </p>

                    <p>
                        <strong>
                        134, TIRUPATI PLAZA, FIRST FLOOR,<br>
                        BESIDE SBPP BANK, CHALA,<br>
                        VAPI, GUJARAT 396191
                        </strong>
                    </p>

                    <p>
                        We will promptly correct any information found to be incorrect.
                    </p>

                    <h2 class="h5 mt-4">Disclaimer</h2>
                    <p>
                        The above content is created at Vrundavan Computers's sole discretion. Razorpay shall not be
                        liable for any content provided here and shall not be responsible for any claims and liability
                        that may arise due to merchant’s non-adherence to it.
                    </p>

                    <div class="text-end mt-4">
                        <a href="{{ route('software.login') }}" class="btn btn-secondary">
                            Back to Login
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection