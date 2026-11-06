@php
    $site = config('site');
    $email = e($site['contact']['email']);
    $privacy = route('privacy');

    $sections = [
        'about-us' => [
            'title' => 'About Mark Properties',
            'body' => '<p>Mark Properties is a real estate marketing and sales partner for builders and developers in Pakistan. We are not the builder or developer of the projects shown on the Website. Any booking, allotment or agreement for a property is made directly between you and the builder.</p>',
        ],
        'information' => [
            'title' => 'Project information',
            'body' => '<p>Project details on the Website, including descriptions, images, renders, amenities, locations, approvals and expected possession dates, are based on information provided by builders and are for general guidance only. They may change without notice and do not form part of any offer or contract.</p>
                <ul>
                    <li>Images and renders may be artist’s impressions and may not show the finished project</li>
                    <li>Possession dates are estimates and depend on the builder</li>
                    <li>Always confirm details, approvals and terms with the builder and in your written agreement before you pay</li>
                </ul>',
        ],
        'no-advice' => [
            'title' => 'No legal or financial advice',
            'body' => '<p>Our guides, articles, FAQs and the views of our advisors are general information. They are not legal, tax or financial advice. Take independent professional advice before making a property or investment decision.</p>',
        ],
        'enquiries' => [
            'title' => 'Enquiries and site visits',
            'body' => '<p>When you send an enquiry or ask for a site visit, you agree that we may contact you by phone, WhatsApp, SMS or email about it, and share your details with the relevant builder as described in our <a href="'.$privacy.'">Privacy Policy</a>. Please give accurate details.</p>',
        ],
        'builders' => [
            'title' => 'Builders and partners',
            'body' => '<p>Builders who list projects with us are responsible for the accuracy of the information they provide and for holding the approvals required to market and sell their projects. Partnership terms are agreed separately in writing.</p>',
        ],
        'acceptable-use' => [
            'title' => 'Using the Website',
            'body' => '<p>You agree not to:</p>
                <ul>
                    <li>Send false, misleading or unlawful information</li>
                    <li>Copy, scrape or republish the Website’s content in bulk without our written permission</li>
                    <li>Interfere with the Website’s security or operation, or try to gain unauthorised access</li>
                    <li>Use the Website to send spam or for any unlawful purpose</li>
                </ul>',
        ],
        'intellectual-property' => [
            'title' => 'Intellectual property',
            'body' => '<p>The Website’s design, text, logos and our own content belong to Mark Properties. Project names, logos and images belong to their respective builders and are used with permission. You may view and share pages for personal, non-commercial use.</p>',
        ],
        'links' => [
            'title' => 'Links to other websites',
            'body' => '<p>The Website links to third-party sites and services, including builders’ websites, Google Maps and social networks. We are not responsible for their content, availability or practices.</p>',
        ],
        'liability' => [
            'title' => 'Limitation of liability',
            'body' => '<p>We work to keep the Website accurate and available, but we provide it “as is” without guarantees. To the extent permitted by law, Mark Properties is not liable for any loss arising from your use of the Website or reliance on its content, or from any agreement between you and a builder, including delays in construction or possession.</p>',
        ],
        'changes' => [
            'title' => 'Changes to these terms',
            'body' => '<p>We may update these terms from time to time. The date at the top of this page shows when they were last changed. Continuing to use the Website means you accept the updated terms.</p>',
        ],
        'law' => [
            'title' => 'Governing law',
            'body' => '<p>These terms are governed by the laws of Pakistan, and the courts at Karachi have jurisdiction over any dispute arising from them.</p>',
        ],
        'contact' => [
            'title' => 'Contacting us',
            'body' => "<p>Questions about these terms? Email <a href=\"mailto:{$email}\">{$email}</a>".'.</p>',
        ],
    ];
@endphp

<x-legal-page title="Terms of Use" updated="2 November 2026" :sections="$sections"
    lead="The terms for using our website, what our project information means, and the limits of our responsibility."
    description="The terms that apply when you use the Mark Properties website, including project information, enquiries and the limits of our liability.">
    <p>These terms apply to your use of {{ $site['domain'] }} (the “Website”), operated by Mark Properties (“we”, “us”, “our”). By using the Website, you agree to them. If you do not agree, please do not use the Website.</p>
</x-legal-page>
