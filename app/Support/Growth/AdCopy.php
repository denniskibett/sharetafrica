<?php

namespace App\Support\Growth;

/**
 * Ready-to-use ad copy per site for its Kenyan search intent (Ad center). Google responsive search ads allow
 * 30-character headlines and 90-character descriptions (tests/Feature/Growth/AdCenterTest checks the limits).
 * Only claims the products actually make: no prices, rates, ratings or guarantees. Edit freely.
 */
class AdCopy
{
    public const COPY = [
        'sharet' => [
            'keywords' => ['mpesa api kenya', 'payment gateway kenya', 'mpesa stk push integration', 'accept mpesa payments online', 'payments api africa', 'mpesa paybill api for developers'],
            'headlines' => ['M-Pesa Payments API', 'Accept M-Pesa Online', 'One API for Kenya Payments', 'STK Push in Minutes', 'Get Sandbox Keys Today', 'Built for Kenyan Developers'],
            'descriptions' => [
                'Collect M-Pesa payments, send payouts and get webhooks from one simple API.',
                'Get sandbox keys today and test M-Pesa STK push before you go live.',
                'Payment requests, payouts and a clear ledger for your business on Sharet.',
            ],
            'meta' => 'Building an app or online shop in Kenya? Sharet gives you one API for M-Pesa collections and payouts. Get your sandbox keys today 👉',
            'whatsapp' => 'Hi! If your business needs to accept M-Pesa online, Sharet has a simple payments API and sandbox keys to test with: ',
            'tiktok' => 'POV: your app takes M-Pesa payments with one API call 💸 Sharet payments API for Kenya. Test it in the sandbox. #Kenya #fintech #MPesa',
        ],
        'breve' => [
            'keywords' => ['invoicing software kenya', 'mpesa invoice', 'send invoice get paid mpesa', 'small business invoicing app kenya', 'online invoice generator kenya', 'receipts and invoices for sme'],
            'headlines' => ['Invoices Paid by M-Pesa', 'Invoicing for Kenyan SMEs', 'Send an Invoice in a Minute', 'Get Paid Faster with M-Pesa', 'Breve Invoicing App', 'Track Who Has Paid You'],
            'descriptions' => [
                'Create an invoice, send the link and let your customer pay by M-Pesa. Made for SMEs.',
                'See who has paid and who has not. Simple invoicing and receipts for small businesses.',
                'Try Breve: professional invoices your customers can pay with M-Pesa.',
            ],
            'meta' => 'Still chasing payments on WhatsApp? Send a Breve invoice your customer can pay by M-Pesa in one tap, and see who has paid. Try it 👉',
            'whatsapp' => 'Hello! For your invoices, try Breve: send an invoice link and your customer pays by M-Pesa. Start here: ',
            'tiktok' => 'Small business hack: send invoices your customers pay with M-Pesa in one tap 🧾✅ Try Breve. #SmallBusinessKenya #MPesa',
        ],
        'aiyotickets' => [
            'keywords' => ['events in nairobi this weekend', 'buy tickets nairobi', 'concerts nairobi', 'event tickets mpesa', 'sell tickets online kenya', 'things to do in nairobi'],
            'headlines' => ['Events in Nairobi', 'Buy Tickets with M-Pesa', 'This Weekend in Nairobi', 'Concerts, Shows & Parties', 'Sell Your Event Tickets', 'aiyotickets Kenya'],
            'descriptions' => [
                'Find what is on in Nairobi and get your ticket in minutes. Pay with M-Pesa.',
                'Organising an event? Sell tickets online and take M-Pesa payments on aiyotickets.',
                'Concerts, shows, parties and more. Browse Nairobi events on aiyotickets today.',
            ],
            'meta' => 'Plans for the weekend? 🎉 See what is on in Nairobi and grab your ticket with M-Pesa on aiyotickets 👉',
            'whatsapp' => 'Hey! Here are this week\'s events in Nairobi, tickets payable with M-Pesa: ',
            'tiktok' => 'Your weekend plans, sorted 🎶 Nairobi events + M-Pesa tickets on aiyotickets. #Nairobi #NairobiEvents #ThingsToDoInNairobi',
        ],
        'eserviz' => [
            'keywords' => ['ntsa logbook transfer', 'ntsa vehicle search', 'land search ardhi house', 'certificate of good conduct application', 'kra returns filing help', 'brs business registration kenya', 'passport application kenya', 'sha nssf helb registration'],
            'headlines' => ['Government Errands Done', 'NTSA Logbook Transfer', 'Land Search at Ardhi House', 'Good Conduct Certificate', 'KRA Returns & BRS Help', 'Skip the Queues: eServiz'],
            'descriptions' => [
                'NTSA logbook transfers, vehicle and land searches handled for you. Pay by M-Pesa.',
                'Certificate of good conduct, passports, KRA returns and BRS registration made simple.',
                'SHA, NSSF and HELB errands without the queues. Book them on eServiz today.',
            ],
            'meta' => 'Tired of government queues? 🏛️ eServiz handles NTSA logbook transfers, land searches, good conduct, KRA returns, passports and more. Book and pay by M-Pesa 👉',
            'whatsapp' => 'Hi! Need a logbook transfer, land search, good conduct certificate or KRA returns done? eServiz handles it for you: ',
            'tiktok' => 'Logbook transfer, land search, good conduct… without the queues 🙌 eServiz runs your government errands. #Kenya #NTSA #KRA',
        ],
        'scharge' => [
            'keywords' => ['rent management software kenya', 'property management system kenya', 'rent collection mpesa', 'landlord app kenya', 'water billing for apartments', 'tenant management kenya'],
            'headlines' => ['Rent Collection by M-Pesa', 'Property Management Kenya', 'Software for Landlords', 'Invoices, Rent & Water Bills', 'Scharge Rent Management', 'Book a Free Demo'],
            'descriptions' => [
                'Bill tenants, collect rent by M-Pesa and track arrears per unit, all in one place.',
                'Water readings, invoices, SMS reminders and reports for Kenyan landlords and agents.',
                'Manage properties, units and tenants on Scharge. Book a demo with our team.',
            ],
            'meta' => 'Landlord or property agent in Kenya? 🏢 Scharge sends rent invoices, collects by M-Pesa and tracks arrears for every unit. Book a demo 👉',
            'whatsapp' => 'Hello! For your rentals: Scharge bills tenants, collects rent by M-Pesa and tracks arrears. Book a demo: ',
            'tiktok' => 'Landlords, stop chasing rent on paper 📒➡️📱 Scharge: invoices, M-Pesa rent and water bills. #Landlord #Kenya #RealEstateKenya',
        ],
        'loans' => [
            'keywords' => ['boda boda loan', 'motorbike loan kenya', 'e-bike financing kenya', 'boda boda financing', 'electric motorbike kenya loan', 'pikipiki loan'],
            'headlines' => ['Boda Boda Loans', 'E-Bike Financing in Kenya', 'Own Your Boda Boda', 'Apply Online in Minutes', 'Sharet Loans', 'Pay Back by M-Pesa'],
            'descriptions' => [
                'Apply for a boda boda or e-bike loan online and repay by M-Pesa. Quick to apply.',
                'Ride your own bike. Sharet Loans finances boda bodas and e-bikes for Kenyan riders.',
                'Check what you need to apply and send your application today on Sharet Loans.',
            ],
            'meta' => 'Ready to own your boda boda or e-bike? 🏍️ Apply online with Sharet Loans and repay by M-Pesa 👉',
            'whatsapp' => 'Habari! Unataka boda boda au e-bike yako? Apply for a Sharet Loans bike loan here: ',
            'tiktok' => 'From renting to owning your boda 🏍️💪 Apply for a bike loan on Sharet Loans, repay by M-Pesa. #BodaBoda #Kenya #EBike',
        ],
        'shoope' => [
            'keywords' => ['safe online payments kenya', 'escrow kenya', 'buy safely online kenya', 'instagram shop payments', 'pay on delivery alternative', 'online shop payment link kenya'],
            'headlines' => ['Safe Payments for Shops', 'Buy Online Without Worry', 'Pay Safely, Get Your Order', 'Shoope Safe Checkout', 'For Instagram & TikTok Shops', 'Sell Online with Trust'],
            'descriptions' => [
                'Shoope holds the payment until the buyer confirms the order. Safer for both sides.',
                'Selling on Instagram, TikTok or WhatsApp? Give buyers a safe way to pay by M-Pesa.',
                'Build trust with new customers: share a Shoope payment link for every order.',
            ],
            'meta' => 'Worried about paying an online shop you found on social media? 🛍️ With Shoope your money is released only when you confirm the order 👉',
            'whatsapp' => 'Hi! To pay for your order safely, use this Shoope link. The seller is paid after you confirm delivery: ',
            'tiktok' => 'Online shopping in Kenya without the fear 😮‍💨 Shoope holds your payment until your order arrives. #ShopSafe #Kenya',
        ],
    ];

    public static function for(string $site): array
    {
        return self::COPY[$site] ?? ['keywords' => [], 'headlines' => [], 'descriptions' => [], 'meta' => '', 'whatsapp' => '', 'tiktok' => ''];
    }
}
