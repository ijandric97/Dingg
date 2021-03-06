<?php

namespace App\Http\Controllers;

use App\Order;
use App\Table;
use Carbon\Carbon;
use App\Restaurant;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OrderRestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Restaurant $restaurant
     * @return View
     * @throws AuthorizationException
     */
    public function index(Restaurant $restaurant)
    {
        $this->authorize('edit-restaurant', $restaurant);

        $colors = [
            '0' => 'text-danger',
            '1' => 'text-success',
            '2' => 'text-dark',
        ];

        return view('pages.restaurant.order.index', [
            'restaurant' => $restaurant,
            'orders' => $restaurant->orders()
                ->orderBy('status', 'desc')
                ->orderBy('reservation_time', 'asc')
                ->paginate(50),
            'colors' => $colors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Restaurant $restaurant
     * @return View|void
     */
    public function create(Restaurant $restaurant)
    {
        // If guest somehow tries to get this, kick him out
        if (!Auth::check()) {
            abort(403);
        }

        // Return create order view
        return view('pages.restaurant.order.create', [
            'restaurant' => $restaurant,
            'user' => Auth::user(),
            'tables' => $restaurant->tables()->get(),
            'groups' => $restaurant->groups()->with('products')->get(),
            'now' => date('Y-m-d\TH:i'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Restaurant $restaurant
     * @return Application|RedirectResponse|Redirector|void
     * @throws ValidationException
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        // If guest somehow tries to get this, kick him out
        if (!Auth::check()) {
            abort(403);
        }

        // Ok lets validate
        // Validate
        $request->validate([
            'table' => 'required|numeric|exists:tables,id',
            'datetime' => 'required|date',

            'id' => 'required|array',
            'id.' => 'numeric',

            'count' => 'required|array',
            'count.' => 'numeric|min:0|max:10',
        ]);

        $datetime = Carbon::parse(request('datetime'));
        $ordertime = $datetime->hour. ':' . $datetime->minute . ':' . $datetime->second;

        // Check if the time is outside workhours
        $workhour = $restaurant->workhours()
            ->where('day_of_week', '=', $datetime->dayOfWeek)
            ->where('open_time', '<', $ordertime)
            ->where('close_time', '>', $ordertime)
            ->first();

        if ($workhour === null) {
            throw ValidationException::withMessages(['datetime' => 'Restaurant is not open at that time!']);
        }

        // Check if the time is not in the past
        if ($datetime->lt(Carbon::now())) {
            throw ValidationException::withMessages(['datetime' => 'You can\'t reserve in the past!']);
        }

        // Check if the time is not at least 30 min from now
        if ($datetime->lt(Carbon::now()->addMinutes(30))) {
            throw ValidationException::withMessages(['datetime' => 'You can\'t reserve less than 30 minutes from now!']);
        }

        $table = Table::findOrFail(request('table'));
        $datetime_start = $datetime->toDateTimeString();
        $datetime_expire = $datetime->addHour()->toDateTimeString();

        // Check if start time is ok
        $occupied = $table->orders()->where('reservation_time', '<=', $datetime_start)
            ->where('expiration_time', '>=', $datetime_start)->get();
        if ($occupied->isNotEmpty()) {
            $retstring = "";

            foreach($occupied as $item) {
                $retstring = $retstring . '(' . $item->reservation_time .' - '. $item->expiration_time . ') ';
            }

            throw ValidationException::withMessages(['datetime' => 'Table is occupied: ' . $retstring]);
        }

        // Check if end time is ok
        $occupied = $table->orders()->where('reservation_time', '<=', $datetime_expire)
            ->where('expiration_time', '>=', $datetime_expire)->get();
        if ($occupied->isNotEmpty()) {
            $retstring = "";

            foreach($occupied as $item) {
                $retstring = $retstring . '(' . $item->reservation_time .' - '. $item->expiration_time . ') ';
            }

            throw ValidationException::withMessages(['datetime' => 'Table is occupied: ' . $retstring]);
        }

        // Create the order
        $order = new Order();
        $order->table_id = request('table');
        $order->user_id = Auth::user()->id;
        $order->restaurant_id = $restaurant->id;
        $order->reservation_time = $datetime_start;
        $order->expiration_time = $datetime_expire;
        $order->save();

        // Connect the order with the products
        $product_count = request('count');
        $product_id = request('id');

        for ($i = 0; $i < count($product_id); $i++) {
            if ($product_count[$i] > 0) {
                $order->products()->attach($product_id[$i], ['count' => $product_count[$i]]);
            }
        }

        return redirect(route('restaurant.show', $restaurant))->with('success', 'Order created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Restaurant $restaurant
     * @param Order $order
     * @return View
     * @throws AuthorizationException
     */
    public function edit(Restaurant $restaurant, Order $order)
    {
        $this->authorize('edit-restaurant', $restaurant);

        $colors = [
            '0' => 'bg-danger',
            '1' => 'bg-success',
            '2' => 'bg-dark',
        ];

        return view('pages.restaurant.order.edit', [
            'restaurant' => $restaurant,
            'order' => $order,
            'table' => $order->table,
            'colors' => $colors,
            'products' => $order->products()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Restaurant $restaurant
     * @param Order $order
     * @return Application|RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function update(Request $request, Restaurant $restaurant, Order $order)
    {
        $this->authorize('edit-restaurant', $restaurant);

        $order->status = 0;
        $order->save();

        return redirect(route('restaurant.order.edit', [$restaurant, $order]))->with('success', 'Order declined');
    }
}
