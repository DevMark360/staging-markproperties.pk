<?php

/*
| Cities of Pakistan, by province / region, for the Partner With Us form: a builder may be launching anywhere,
| including cities where no project is listed yet. Keys come from App\Support\PakistanCities (slug of the name),
| so the cities already on the site keep their slugs (karachi, lahore, islamabad…).
|
| Not used for browsing: the project lists only offer cities with live projects (Projects::liveCities()).
*/

return [
    'Islamabad Capital Territory' => ['Islamabad'],

    'Punjab' => [
        'Ahmedpur East', 'Arifwala', 'Attock', 'Bahawalnagar', 'Bahawalpur', 'Bhakkar', 'Burewala', 'Chakwal',
        'Chiniot', 'Chishtian', 'Daska', 'Dera Ghazi Khan', 'Dina', 'Faisalabad', 'Gojra', 'Gujranwala', 'Gujrat',
        'Hafizabad', 'Hasilpur', 'Jaranwala', 'Jhang', 'Jhelum', 'Kamalia', 'Kamoke', 'Kasur', 'Khanewal', 'Khanpur',
        'Kharian', 'Khushab', 'Kot Addu', 'Lahore', 'Layyah', 'Lodhran', 'Mandi Bahauddin', 'Mianwali', 'Multan',
        'Muridke', 'Murree', 'Muzaffargarh', 'Narowal', 'Okara', 'Pakpattan', 'Rahim Yar Khan', 'Rajanpur',
        'Rawalpindi', 'Sadiqabad', 'Sahiwal', 'Samundri', 'Sargodha', 'Shakargarh', 'Sheikhupura', 'Sialkot',
        'Taxila', 'Toba Tek Singh', 'Vehari', 'Wah Cantt', 'Wazirabad',
    ],

    'Sindh' => [
        'Badin', 'Dadu', 'Ghotki', 'Hyderabad', 'Jacobabad', 'Jamshoro', 'Kandhkot', 'Karachi', 'Khairpur', 'Larkana',
        'Mirpur Khas', 'Nawabshah', 'Sanghar', 'Shikarpur', 'Sukkur', 'Tando Adam', 'Tando Allahyar', 'Thatta',
        'Umerkot',
    ],

    'Khyber Pakhtunkhwa' => [
        'Abbottabad', 'Bannu', 'Battagram', 'Charsadda', 'Chitral', 'Dera Ismail Khan', 'Haripur', 'Kohat',
        'Mansehra', 'Mardan', 'Mingora', 'Nowshera', 'Peshawar', 'Swabi',
    ],

    'Balochistan' => ['Chaman', 'Gwadar', 'Hub', 'Khuzdar', 'Loralai', 'Quetta', 'Turbat', 'Zhob'],

    'Gilgit-Baltistan' => ['Gilgit', 'Hunza', 'Skardu'],

    'Azad Jammu & Kashmir' => ['Bagh', 'Kotli', 'Mirpur', 'Muzaffarabad', 'Rawalakot'],
];
