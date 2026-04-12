@extends('layouts.auth2')
@section('title', 'Terms and Conditions')
@inject('request', 'Illuminate\Http\Request')
@section('content')
<div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pt-20 tw-pb-10 tw-px-5">
    <div class="tw-max-w-5xl tw-mx-auto tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-6 md:tw-p-10 tw-leading-7 tw-text-gray-800">
        <h1 class="tw-text-3xl md:tw-text-4xl tw-font-bold tw-text-gray-900">Terms and Conditions</h1>
        <p class="tw-mt-2"><strong>Last Updated:</strong> April 2026</p>

        <p class="tw-mt-4">
            Welcome to Sector71 Softwares ("Sector71", "we", "our", or "us"). These Terms and Conditions ("Terms")
            govern your access to and use of the Sector71 Cloud POS software, website
            (<a href="https://sector71.app/" target="_blank" rel="noopener">https://sector71.app/</a>), and related services
            (collectively, the "Service").
        </p>
        <p class="tw-mt-3">
            By registering an account, accessing, or using our Service, you agree to be bound by these Terms.
            If you do not agree, you may not use the Service.
        </p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">1. Account Registration &amp; Security</h2>
        <p class="tw-mt-3"><strong>Eligibility:</strong> You must be at least 18 years old and capable of forming a binding contract to register for our Service.</p>
        <p class="tw-mt-3"><strong>Accuracy of Information:</strong> You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate.</p>
        <p class="tw-mt-3"><strong>Account Security:</strong> You are responsible for safeguarding your account credentials (username and password). Sector71 is not liable for any loss or damage arising from your failure to protect your login information. You must notify us immediately of any unauthorized use of your account.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">2. Subscription, Billing, and Payments</h2>
        <p class="tw-mt-3"><strong>Subscription Fees:</strong> Access to Sector71 is billed on a subscription basis (e.g., monthly or annually). By subscribing, you agree to pay the applicable fees (such as ৳999/month or equivalent international pricing) as displayed at the time of purchase.</p>
        <p class="tw-mt-3"><strong>Payment Processing:</strong> We use secure third-party payment gateways for both domestic (Bangladesh) and international transactions. You authorize us to charge your selected payment method for the subscription fees.</p>
        <p class="tw-mt-3"><strong>Renewals &amp; Cancellation:</strong> Subscriptions automatically renew unless canceled before the next billing cycle. You may cancel your subscription at any time. Payments are non-refundable, except where required by law.</p>
        <p class="tw-mt-3"><strong>Service Suspension:</strong> We reserve the right to suspend or terminate your account if your subscription payment is overdue.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">3. Acceptable Use Policy</h2>
        <p class="tw-mt-3">You agree to use the Service only for lawful business purposes. You shall not:</p>
        <ul class="tw-list-disc tw-pl-6 tw-mt-2 tw-space-y-1">
            <li>Use the Service for any illegal, fraudulent, or unauthorized purpose.</li>
            <li>Interfere with or disrupt the security, integrity, or performance of the Service.</li>
            <li>Attempt to reverse-engineer, decompile, hack, or copy the software.</li>
            <li>Upload or transmit viruses, malware, or any malicious code.</li>
        </ul>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">4. Data Ownership and Privacy</h2>
        <p class="tw-mt-3"><strong>Your Data:</strong> You retain full ownership of all data, customer lists, inventory details, and financial records you input into Sector71 ("User Data").</p>
        <p class="tw-mt-3"><strong>Data Security:</strong> We implement industry-standard security measures, including cloud backups, to protect your User Data. However, no system is 100% secure, and we cannot guarantee absolute security.</p>
        <p class="tw-mt-3"><strong>Privacy:</strong> Our data collection and use practices are governed by our Privacy Policy. By using the Service, you consent to the processing of your data as described therein.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">5. Intellectual Property Rights</h2>
        <p class="tw-mt-3">All intellectual property rights in the Service, including the software, source code, UI/UX design, and logos (the "Sector71 Brand"), are the exclusive property of Sector71 Softwares. Your subscription grants you a limited, non-exclusive, non-transferable license to use the software. You do not acquire any ownership rights.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">6. Limitation of Liability &amp; Disclaimer of Warranties</h2>
        <p class="tw-mt-3"><strong>"As Is" Basis:</strong> The Service is provided on an "AS IS" and "AS AVAILABLE" basis. Sector71 disclaims all warranties, express or implied, including fitness for a particular purpose and non-infringement.</p>
        <p class="tw-mt-3"><strong>Limitation of Liability:</strong> To the maximum extent permitted by law, Sector71 shall not be liable for any indirect, incidental, special, or consequential damages, including but not limited to loss of profits, data, or business interruption, arising out of your use or inability to use the Service.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">7. Termination</h2>
        <p class="tw-mt-3">We may suspend or terminate your access to the Service immediately, without prior notice or liability, for any reason whatsoever, including if you breach these Terms. Upon termination, your right to use the Service will immediately cease, and your data may be deleted according to our data retention policy.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">8. Governing Law and Jurisdiction</h2>
        <p class="tw-mt-3">These Terms shall be governed by and construed in accordance with the laws of Bangladesh, without regard to its conflict of law provisions. For international users, local laws regarding data protection and consumer rights may also apply. Any disputes arising from these terms will be subject to the exclusive jurisdiction of the courts in Dhaka, Bangladesh.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">9. Changes to Terms</h2>
        <p class="tw-mt-3">We reserve the right to modify or replace these Terms at any time. We will notify users of any significant changes via email or an in-app notification. Continued use of the Service after changes are effective constitutes your acceptance of the new Terms.</p>

        <h2 class="tw-text-xl tw-font-semibold tw-mt-8">10. Contact Us</h2>
        <p class="tw-mt-3">If you have any questions about these Terms, please contact us at:</p>
        <p class="tw-mt-2"><strong>Email:</strong> <a href="mailto:sector71.owner@gmail.com">sector71.owner@gmail.com</a></p>
        <p class="tw-mt-1"><strong>WhatsApp/Phone:</strong> <a href="tel:+8801336313953">+8801336313953</a></p>
        <p class="tw-mt-1"><strong>Website:</strong> <a href="https://sector71.app/" target="_blank" rel="noopener">https://sector71.app/</a></p>
    </div>
</div>
@endsection
            