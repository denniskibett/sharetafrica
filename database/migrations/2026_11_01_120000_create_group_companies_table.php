<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Businesses Sharet owns and runs (/company#companies and /companies/{slug}), edited in Website › Companies.
 * Seeded from what each company's own website said on 31 Oct 2026. Not to be confused with `companies`
 * (the businesses that use Sharet).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('group_companies')) {
            return;
        }

        Schema::create('group_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sector')->nullable();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->json('services')->nullable();
            $table->string('served')->nullable();
            $table->string('url')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['Breve', 'breve', 'Business software', 'The whole business, on one screen.',
                'Business management for small and growing businesses in Kenya and across Africa. One app runs quotes, jobs, invoices, M-Pesa payments, stock, suppliers and payroll, in the words of your own industry. Free for one user.',
                ['Quotes, contracts with e-signature, and invoices with M-Pesa prompts and automatic reminders', 'Field jobs with checklists, photos and customer sign-off, even offline', 'Purchase orders, supplier price comparison and stock levels', 'Payroll with PAYE, NSSF, SHIF and the housing levy, plus Uganda, Tanzania, Rwanda, Nigeria and Ghana', 'KRA PIN and VAT on every document'],
                'Kenya first, then across Africa', 'https://breve.sharet.africa/', null, null],
            ['Aimzone Ventures', 'aimzone-ventures', 'Finance', 'Emergency funding and trade finance in Kenya.',
                'Fast, discreet finance for businesses and individuals from Nairobi: emergency bridge finance, asset-backed loans, credit bureau (CRB) clearance and trade finance for importers and exporters.',
                ['Emergency bridge finance from KES 5,000, processed in 30 to 45 minutes, 24/7', 'Asset-backed loans against property, vehicles or machinery', 'CRB clearance and bank clearance certificates', 'Trade finance: letters of credit, purchase-order finance, agricultural commodity finance'],
                'East Africa · The Atrium, Lenana Road, Nairobi', 'https://loans.sharet.africa/', 'hello@aimzoneventures.com', '+254 701 607 959'],
            ['scharge', 'scharge', 'Property', 'Property management for Africa, starting in Kenya.',
                'Collects rent, service charge, water and bookings by M-Pesa and bank, and runs repairs, payouts and reports. Built for developers, landlords, managers, short-stay hosts, hotels, offices and warehouses. Free for tenants.',
                ['Automatic M-Pesa and bank collection matched to each unit, with SMS and WhatsApp arrears reminders', 'Metered water and power billing backed by meter photos', 'Airbnb and Booking.com sync, smart-lock codes and cleaner checklists', 'Developer handover: off-plan instalments, snag lists and service charge from day one', 'Warehouse bay leases with gate and truck logs'],
                'Kenya now; Uganda, Tanzania and Rwanda next', 'https://scharge.sharet.africa/', 'hello@scharge.com', null],
            ['aiyotickets', 'aiyotickets', 'Tickets & experiences', 'Event and sports tickets in Kenya, paid with M-Pesa.',
                'Tickets for concerts, comedy, football, rugby, marathons and festivals, plus travel experiences and RSVP invitations for weddings and corporate events. Pay by M-Pesa, even bit by bit, and get a QR ticket by SMS.',
                ['M-Pesa checkout with "lipa mdogo mdogo" instalments and QR tickets by SMS', 'Experiences: safaris and race weekends with deposits and pick-up points', 'Invites: RSVPs, seating plans, QR check-in and M-Pesa gifts', 'For organisers: gate scanning on any phone, payouts to M-Pesa or bank, advances on sales, white label'],
                'Kenya, with events across East Africa', 'https://aiyotickets.com/', 'hello@aiyotickets.com', null],
            ['eServiz', 'eserviz', 'Government services', 'Government errands, done for you.',
                'Connects Kenyans with verified officers and brokers at NTSA, Lands, KRA, NRB and DCI to run government errands for them. Payment is by M-Pesa and held in escrow until you approve the work. eServiz is a private service, not a government site.',
                ['Logbook transfers, land searches, Certificate of Good Conduct, KRA compliance and returns', 'Fixed price up front: government fee plus service charge', 'No account needed: your phone number is your ID, with request tracking', 'Escrow with disputes and refunds; officers cash out to M-Pesa'],
                'Kenya (Nairobi)', 'https://eserviz.aiyotickets.com/', 'support@eserviz.co.ke', null],
            ['Oteli', 'oteli', 'Hospitality', 'Hotel management, running The Willis Hotel in Bomet.',
                'Oteli runs The Willis Hotel on the Sotik–Narok Highway in Bomet: rooms, dining, conferences and events for business and leisure travellers, and a base for the Maasai Mara, the Mau forest and the tea highlands.',
                ['Double, Premium King, Deluxe and Family rooms', 'Conferences and events, including weddings', 'Restaurant and bar', 'Tours around Bomet'],
                'Bomet County, Kenya', 'http://thewillishotel.co.ke/', 'hotelthewillis@gmail.com', '0758 000 010'],
            ['Shoope', 'shoope', 'Coming soon', 'Launching soon.',
                'Shoope is a new Sharet company, launching soon. Details will be added here when it opens.',
                [], null, 'https://shoope.africa/', null, null],
        ];

        foreach ($rows as $i => [$name, $slug, $sector, $tagline, $description, $services, $served, $url, $email, $phone]) {
            DB::table('group_companies')->insert(compact('name', 'slug', 'sector', 'tagline', 'description', 'served', 'url', 'email', 'phone') + [
                'services' => json_encode($services), 'sort' => $i + 1, 'published' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_companies');
    }
};
