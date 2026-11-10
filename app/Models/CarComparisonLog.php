<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarComparisonLog extends Model
{
    protected $fillable = [
        'car_comparison_id',
        'user_id',
        'action',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carComparison()
    {
        return $this->belongsTo(CarVendorComparison::class, 'car_comparison_id');
    }

    public function actionIcon(): string
    {
        return match ($this->action) {
            'submitted'               => 'bi-send text-primary',
            'submitted_supervisor'    => 'bi-send text-primary',
            'approved_supervisor'     => 'bi-check-circle text-info',
            'approved_manager'        => 'bi-check-circle-fill text-success',
            'approved_manager_bypass' => 'bi-skip-forward-circle-fill text-success',
            'rejected'                => 'bi-x-circle-fill text-danger',
            'edited'                  => 'bi-pencil text-warning',
            'odoo_posted'             => 'bi-cloud-check text-info',
            'cancelled'               => 'bi-slash-circle text-secondary',
            default                   => 'bi-info-circle text-secondary',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'submitted'               => 'Submitted for Supervisor Approval',
            'submitted_supervisor'    => 'Submitted by Supervisor',
            'approved_supervisor'     => 'Approved by Supervisor',
            'approved_manager'        => 'Approved by Manager',
            'approved_manager_bypass' => 'Approved by Manager (Bypass)',
            'rejected'                => 'Rejected',
            'edited'                  => 'Edited',
            'odoo_posted'             => 'Posted to Odoo',
            'cancelled'               => 'Cancelled',
            default                   => ucfirst($this->action),
        };
    }
}

