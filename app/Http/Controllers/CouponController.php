<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-list')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = 'Coupons';
        $coupons = Coupon::orderByDesc('id')->paginate(20);

        return view('coupon.index', compact('pageTitle', 'coupons'));
    }

    public function create()
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-add')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $pageTitle = 'Add Coupon';
        return view('coupon.form', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-add')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $data = $request->validate([
            'code' => 'required|string|max:255|unique:coupons,code',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        Coupon::create($data);

        return redirect()->route('coupons.index')->withSuccess('Coupon saved successfully.');
    }

    public function edit($id)
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-edit')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $coupon = Coupon::findOrFail($id);
        $pageTitle = 'Edit Coupon';

        return view('coupon.form', compact('coupon', 'pageTitle'));
    }

    public function update(Request $request, $id)
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-edit')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'code' => 'required|string|max:255|unique:coupons,code,' . $coupon->id,
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $coupon->update($data);

        return redirect()->route('coupons.index')->withSuccess('Coupon updated successfully.');
    }

    public function destroy($id)
    {
        $auth_user = AuthHelper::authSession();
        if (!$auth_user->can('package-delete')) {
            $message = __('message.permission_denied_for_account');
            return redirect()->back()->withErrors($message);
        }

        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('coupons.index')->withSuccess('Coupon deleted successfully.');
    }
}
