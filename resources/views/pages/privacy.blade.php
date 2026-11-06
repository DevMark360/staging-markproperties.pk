@php
    $site = config('site');
    $email = e($site['contact']['email']);
    $address = e($site['contact']['address']);

    $sections = [
        'information-we-collect' => [
            'title' => 'Information we collect',
            'body' => '<p>You can browse the Website without telling us who you are. When you contact us or use one of our forms, we collect the information you choose to give us, which may include:</p>
                <ul>
                    <li>Your name, phone number and email address</li>
                    <li>Your city and area, and the project or topic you are asking about</li>
                    <li>Anything you write in a message or enquiry</li>
                    <li>For builders, your company name and details of your project</li>
                </ul>
                <p>We do not ask for, and you should not send us, CNIC numbers, bank details or other sensitive documents through the Website.</p>',
        ],
        'how-we-collect' => [
            'title' => 'How we collect it',
            'body' => '<ul>
                    <li>Contact, enquiry, brochure and partnership forms on the Website</li>
                    <li>Launch-alert sign-ups</li>
                    <li>Emails you send us</li>
                    <li>Basic technical information your browser sends automatically, such as your IP address, browser type and the pages you visit</li>
                </ul>',
        ],
        'how-we-use' => [
            'title' => 'How we use your information',
            'body' => '<p>We use your information to:</p>
                <ul>
                    <li>Respond to your enquiry and call or message you back</li>
                    <li>Arrange site visits and share details of the projects you ask about</li>
                    <li>Send launch alerts and project updates you have signed up for</li>
                    <li>Discuss partnerships with builders who contact us</li>
                    <li>Keep the Website secure, prevent spam and improve how it works</li>
                </ul>',
        ],
        'sharing' => [
            'title' => 'Sharing your information',
            'body' => '<p>We do not sell your personal information. We share it only when needed to help you, for example:</p>
                <ul>
                    <li>With the builder of a project you have asked about, so they can answer your questions or process a booking you choose to make</li>
                    <li>With service providers who host the Website or deliver our messages, under instructions to keep it confidential</li>
                    <li>When required by law, or to protect our rights, our users or the public</li>
                </ul>',
        ],
        'retention' => [
            'title' => 'How long we keep it',
            'body' => '<p>We keep your information for as long as we need it to deal with your enquiry and any follow-up, and for as long as the law requires. When it is no longer needed, we delete it or make it anonymous.</p>',
        ],
        'cookies' => [
            'title' => 'Cookies',
            'body' => '<p>The Website uses a small number of essential cookies to keep it working and secure, for example to protect our forms from misuse. Pages that show a map load it from Google Maps or from OpenFreeMap (map data from OpenStreetMap), which may set their own cookies or receive your IP address under their own policies. You can block or delete cookies in your browser settings, although some parts of the Website may then not work properly.</p>',
        ],
        'marketing' => [
            'title' => 'Launch alerts and marketing',
            'body' => '<p>We send launch alerts and project updates only if you sign up for them. You can stop them at any time by replying to one of our messages or contacting us using the details below.</p>',
        ],
        'third-party' => [
            'title' => 'Links to other websites',
            'body' => '<p>The Website links to other sites and services, such as builders’ websites, Google Maps and social networks. We are not responsible for how they handle your information, so please read their privacy policies.</p>',
        ],
        'security' => [
            'title' => 'Keeping your information safe',
            'body' => '<p>We take reasonable steps to protect your information from loss, misuse and unauthorised access. No website or online service can be completely secure, so we cannot guarantee the security of information sent to us over the internet.</p>',
        ],
        'children' => [
            'title' => 'Children',
            'body' => '<p>The Website is intended for adults. We do not knowingly collect information from anyone under 18. If you believe a child has sent us their information, please contact us and we will delete it.</p>',
        ],
        'your-choices' => [
            'title' => 'Your choices',
            'body' => '<p>You can ask us to show you the information we hold about you, correct it, or delete it. We may keep some information where the law requires us to.</p>',
        ],
        'changes' => [
            'title' => 'Changes to this policy',
            'body' => '<p>We may update this policy from time to time. The date at the top of this page shows when it was last changed.</p>',
        ],
        'contact' => [
            'title' => 'Contacting us',
            'body' => "<p>If you have a question about this policy or your information, contact us:</p>
                <ul>
                    <li>Email: <a href=\"mailto:{$email}\">{$email}</a></li>
                    
                    <li>Address: {$address}</li>
                </ul>",
        ],
    ];
@endphp

<x-legal-page title="Privacy Policy" updated="5 November 2026" :sections="$sections"
    lead="What we collect when you use our website, what we do with it, how long we keep it, and how to reach us about it."
    description="How Mark Properties collects, uses and protects the personal information you share through our website, forms and enquiries.">
    <p>This policy explains how Mark Properties (“we”, “us”, “our”) handles personal information collected through {{ $site['domain'] }} (the “Website”) and when you contact us. By using the Website, you agree to this policy.</p>
</x-legal-page>
