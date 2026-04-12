@extends('cms::frontend.layouts.app')
@section('title', 'Terms and Conditions')
@php
    $navbar_btn['text'] = 'Try For Free';
    $navbar_btn['link'] = route('business.getRegister');

    if (isset($__site_details['btns']) && isset($__site_details['btns']['navbar']) && !empty($__site_details['btns']['navbar']['text'])) {
        $navbar_btn['text'] = $__site_details['btns']['navbar']['text'] ?? 'Try For Free';
    }

    if (isset($__site_details['btns']) && isset($__site_details['btns']['navbar']) && !empty($__site_details['btns']['navbar']['link'])) {
        $navbar_btn['link'] = $__site_details['btns']['navbar']['link'] ?? route('business.getRegister');
    }
@endphp
@includeIf('cms::frontend.layouts.header')

@section('meta')
<meta name="description" content="Terms and Conditions for Sector71 Cloud POS services.">
@endsection

@section('css')
<style>
    .s71-terms-wrap {
        background: #f4f8ff;
        padding: 48px 0;
    }

    .s71-terms-card {
        background: #ffffff;
        border: 1px solid #dbe6f7;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(7, 33, 72, 0.08);
        padding: 28px;
    }

    .s71-terms-card h1,
    .s71-terms-card h2 {
        color: #0d2f5f;
    }

    .s71-terms-card h2 {
        font-size: 1.2rem;
        margin-top: 1.6rem;
        margin-bottom: 0.7rem;
    }

    .s71-terms-card p,
    .s71-terms-card li {
        color: #24364f;
        line-height: 1.75;
    }

    .s71-terms-card a {
        color: #0f63d8;
        text-decoration: none;
    }

    .s71-terms-card a:hover {
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="s71-terms-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="s71-terms-card">
                    <h1 class="mb-2">Terms and Conditions</h1>
                    <p class="mb-4"><strong>Last Updated:</strong> April 2026</p>

                    <p>
                        Welcome to Sector71 Softwares ("Sector71", "we", "our", or "us"). These Terms and Conditions
                        ("Terms") govern your access to and use of the Sector71 Cloud POS software, website
                        (<a href="https://sector71.app/" target="_blank" rel="noopener">https://sector71.app/</a>), and related
                        services (collectively, the "Service").
                    </p>
                    <p>
                        By registering an account, accessing, or using our Service, you agree to be bound by these Terms.
                        If you do not agree, you may not use the Service.
                    </p>

                    <h2>1. Account Registration and Security</h2>
                    <p><strong>Eligibility:</strong> You must be at least 18 years old and capable of forming a binding contract to register for our Service.</p>
                    <p><strong>Accuracy of Information:</strong> You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate.</p>
                    <p><strong>Account Security:</strong> You are responsible for safeguarding your account credentials (username and password). Sector71 is not liable for any loss or damage arising from your failure to protect your login information. You must notify us immediately of any unauthorized use of your account.</p>

                    <h2>2. Subscription, Billing, and Payments</h2>
                    <p><strong>Subscription Fees:</strong> Access to Sector71 is billed on a subscription basis (such as monthly or annually). By subscribing, you agree to pay the applicable fees as displayed at the time of purchase.</p>
                    <p><strong>Payment Processing:</strong> We use secure third-party payment gateways for both domestic (Bangladesh) and international transactions. You authorize us to charge your selected payment method for subscription fees.</p>
                    <p><strong>Renewals and Cancellation:</strong> Subscriptions automatically renew unless canceled before the next billing cycle. You may cancel your subscription at any time. Payments are non-refundable, except where required by law.</p>
                    <p><strong>Service Suspension:</strong> We reserve the right to suspend or terminate your account if your subscription payment is overdue.</p>

                    <h2>3. Acceptable Use Policy</h2>
                    <p>You agree to use the Service only for lawful business purposes. You shall not:</p>
                    <ul>
                        <li>Use the Service for any illegal, fraudulent, or unauthorized purpose.</li>
                        <li>Interfere with or disrupt the security, integrity, or performance of the Service.</li>
                        <li>Attempt to reverse-engineer, decompile, hack, or copy the software.</li>
                        <li>Upload or transmit viruses, malware, or any malicious code.</li>
                    </ul>

                    <h2>4. Data Ownership and Privacy</h2>
                    <p><strong>Your Data:</strong> You retain full ownership of all data, customer lists, inventory details, and financial records you input into Sector71 ("User Data").</p>
                    <p><strong>Data Security:</strong> We implement industry-standard security measures, including cloud backups, to protect your User Data. However, no system is 100 percent secure, and we cannot guarantee absolute security.</p>
                    <p><strong>Privacy:</strong> Our data collection and use practices are governed by our Privacy Policy. By using the Service, you consent to the processing of your data as described therein.</p>

                    <h2>5. Intellectual Property Rights</h2>
                    <p>All intellectual property rights in the Service, including the software, source code, UI and UX design, and logos (the "Sector71 Brand"), are the exclusive property of Sector71 Softwares. Your subscription grants you a limited, non-exclusive, non-transferable license to use the software. You do not acquire any ownership rights.</p>

                    <h2>6. Limitation of Liability and Disclaimer of Warranties</h2>
                    <p><strong>As Is Basis:</strong> The Service is provided on an "AS IS" and "AS AVAILABLE" basis. Sector71 disclaims all warranties, express or implied, including fitness for a particular purpose and non-infringement.</p>
                    <p><strong>Limitation of Liability:</strong> To the maximum extent permitted by law, Sector71 shall not be liable for any indirect, incidental, special, or consequential damages, including but not limited to loss of profits, data, or business interruption, arising out of your use or inability to use the Service.</p>

                    <h2>7. Termination</h2>
                    <p>We may suspend or terminate your access to the Service immediately, without prior notice or liability, for any reason whatsoever, including if you breach these Terms. Upon termination, your right to use the Service will immediately cease, and your data may be deleted according to our data retention policy.</p>

                    <h2>8. Governing Law and Jurisdiction</h2>
                    <p>These Terms shall be governed by and construed in accordance with the laws of Bangladesh, without regard to its conflict of law provisions. For international users, local laws regarding data protection and consumer rights may also apply. Any disputes arising from these terms will be subject to the exclusive jurisdiction of the courts in Dhaka, Bangladesh.</p>

                    <h2>9. Changes to Terms</h2>
                    <p>We reserve the right to modify or replace these Terms at any time. We will notify users of any significant changes via email or in-app notification. Continued use of the Service after changes are effective constitutes your acceptance of the new Terms.</p>

                    <h2>10. Contact Us</h2>
                    <p>If you have any questions about these Terms, please contact us at:</p>
                    <p class="mb-1"><strong>Email:</strong> <a href="mailto:sector71.owner@gmail.com">sector71.owner@gmail.com</a></p>
                    <p class="mb-1"><strong>WhatsApp and Phone:</strong> <a href="tel:+8801336313953">+8801336313953</a></p>
                    <p class="mb-0"><strong>Website:</strong> <a href="https://sector71.app/" target="_blank" rel="noopener">https://sector71.app/</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
