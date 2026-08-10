<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\Enquiry;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Pet;
use App\Models\Referral;
use App\Models\Subscription;
use App\Models\Wishlist;
use App\Services\Growth\LoyaltyService;
use App\Services\Growth\ReferralService;
use App\Services\SubscriptionService;

final class AccountController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions = new SubscriptionService(),
        private readonly LoyaltyService $loyalty = new LoyaltyService(),
        private readonly ReferralService $referrals = new ReferralService(),
    ) {
    }

    public function index(Request $request): void
    {
        $userId = (int) App::auth()->id();

        $this->view('site/account/index', [
            'title' => 'My account',
            'user' => App::auth()->user(),
            'recentOrders' => array_slice(Order::forUser($userId), 0, 3),
            'pets' => Pet::forUser($userId),
            'wishlistCount' => count(Wishlist::forUser($userId)),
        ]);
    }

    public function orders(Request $request): void
    {
        $this->view('site/account/orders', [
            'title' => 'My orders',
            'orders' => Order::forUser((int) App::auth()->id()),
        ]);
    }

    public function pets(Request $request): void
    {
        $this->view('site/account/pets', [
            'title' => 'My pets',
            'pets' => Pet::forUser((int) App::auth()->id()),
        ]);
    }

    public function storePet(Request $request): void
    {
        $userId = (int) App::auth()->id();
        $data = $request->only(['name', 'species', 'breed', 'sex', 'birthday', 'weight_kg', 'allergies', 'notes']);

        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'species' => 'required|in:dog,cat,bird,fish,small_pet,other',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the pet details.');
            back();
        }

        Pet::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'species' => $data['species'],
            'breed' => !empty($data['breed']) ? $data['breed'] : null,
            'sex' => !empty($data['sex']) ? $data['sex'] : 'unknown',
            'birthday' => !empty($data['birthday']) ? $data['birthday'] : null,
            'weight_grams' => is_numeric($data['weight_kg'] ?? null) ? (int) round(((float) $data['weight_kg']) * 1000) : null,
            'allergies' => !empty($data['allergies']) ? $data['allergies'] : null,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
        ]);

        flash('success', $data['name'] . ' has been added.');
        $this->redirect('/account/pets');
    }

    public function destroyPet(Request $request, string $id): void
    {
        $userId = (int) App::auth()->id();
        $pet = Pet::find((int) $id);

        if ($pet === null || (int) $pet['user_id'] !== $userId) {
            abort(404);
        }

        Pet::destroy((int) $id);
        flash('success', 'Pet profile removed.');
        $this->redirect('/account/pets');
    }

    public function addresses(Request $request): void
    {
        $this->view('site/account/addresses', [
            'title' => 'My addresses',
            'addresses' => Address::forUser((int) App::auth()->id()),
        ]);
    }

    public function storeAddress(Request $request): void
    {
        $userId = (int) App::auth()->id();
        $data = $request->only(['label', 'full_name', 'phone', 'line1', 'line2', 'city', 'state', 'postal_code', 'is_default']);

        $validator = Validator::make($data, [
            'full_name' => 'required|max:150',
            'phone' => 'required|max:20',
            'line1' => 'required|max:200',
            'city' => 'required|max:100',
            'state' => 'required|max:100',
            'postal_code' => 'required|max:12',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the address details.');
            back();
        }

        if (!empty($data['is_default'])) {
            Database::instance()->update('addresses', ['is_default' => 0], 'user_id = :uid', ['uid' => $userId]);
        }

        Address::create([
            'user_id' => $userId,
            'label' => !empty($data['label']) ? $data['label'] : 'Home',
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'line1' => $data['line1'],
            'line2' => !empty($data['line2']) ? $data['line2'] : null,
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'is_default' => !empty($data['is_default']) ? 1 : 0,
        ]);

        flash('success', 'Address saved.');
        $this->redirect('/account/addresses');
    }

    public function destroyAddress(Request $request, string $id): void
    {
        $userId = (int) App::auth()->id();
        $address = Address::find((int) $id);

        if ($address === null || (int) $address['user_id'] !== $userId) {
            abort(404);
        }

        Address::destroy((int) $id);
        flash('success', 'Address removed.');
        $this->redirect('/account/addresses');
    }

    public function wishlist(Request $request): void
    {
        $this->view('site/account/wishlist', [
            'title' => 'My wishlist',
            'items' => Wishlist::forUser((int) App::auth()->id()),
        ]);
    }

    public function support(Request $request): void
    {
        $this->view('site/account/support', [
            'title' => 'Support',
            'tickets' => Enquiry::forUser((int) App::auth()->id()),
        ]);
    }

    public function storeSupport(Request $request): void
    {
        $user = App::auth()->user();
        $data = $request->only(['subject', 'message', 'order_number']);

        $validator = Validator::make($data, [
            'subject' => 'required|max:200',
            'message' => 'required|max:2000',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please fill in the subject and message.');
            back();
        }

        Enquiry::create([
            'user_id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'order_number' => !empty($data['order_number']) ? $data['order_number'] : null,
        ]);

        flash('success', 'Your message has been sent. We\'ll reply by email.');
        $this->redirect('/account/support');
    }

    public function appointments(Request $request): void
    {
        $this->view('site/account/appointments', [
            'title' => 'My appointments',
            'appointments' => Appointment::forUser((int) App::auth()->id()),
        ]);
    }

    public function subscriptions(Request $request): void
    {
        $this->view('site/account/subscriptions', [
            'title' => 'My subscriptions',
            'subscriptions' => Subscription::forUser((int) App::auth()->id()),
        ]);
    }

    public function storeSubscription(Request $request): void
    {
        $userId = (int) App::auth()->id();
        $data = $request->only(['product_id', 'variant_id', 'quantity', 'interval_days']);

        $validator = Validator::make($data, [
            'product_id' => 'required|integer',
            'variant_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            flash('error', 'Please choose a product option to subscribe.');
            back();
        }

        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $intervalDays = max(7, (int) ($data['interval_days'] ?? 30));

        $sub = $this->subscriptions->create($userId, (int) $data['product_id'], (int) $data['variant_id'], $quantity, $intervalDays, null);

        flash('success', 'Subscription created. First reminder goes out on ' . date('d M Y', strtotime((string) $sub['next_order_date'])) . '.');
        $this->redirect('/account/subscriptions');
    }

    public function pauseSubscription(Request $request, string $id): void
    {
        $this->handleSubscriptionAction($id, 'pause');
    }

    public function resumeSubscription(Request $request, string $id): void
    {
        $this->handleSubscriptionAction($id, 'resume');
    }

    public function skipSubscription(Request $request, string $id): void
    {
        $this->handleSubscriptionAction($id, 'skipNext');
    }

    public function cancelSubscription(Request $request, string $id): void
    {
        $this->handleSubscriptionAction($id, 'cancel');
    }

    private function handleSubscriptionAction(string $id, string $method): void
    {
        $userId = (int) App::auth()->id();

        try {
            $this->subscriptions->{$method}((int) $id, $userId);
            flash('success', 'Subscription updated.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        }

        $this->redirect('/account/subscriptions');
    }

    public function rewards(Request $request): void
    {
        $userId = (int) App::auth()->id();

        $this->view('site/account/rewards', [
            'title' => 'Rewards',
            'balance' => $this->loyalty->balance($userId),
            'tier' => $this->loyalty->tier($userId),
            'ledger' => LoyaltyPoint::ledgerFor($userId),
            'referralCode' => $this->referrals->getOrCreateCode($userId),
            'referrals' => Referral::forReferrer($userId),
        ]);
    }
}
