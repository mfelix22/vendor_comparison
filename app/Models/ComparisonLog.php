<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComparisonLog extends Model
{
    protected $fillable = ['comparison_id', 'user_id', 'action', 'notes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comparison()
    {
        return $this->belongsTo(VendorComparison::class);
    }

    /**
     * Icon class for each action type.
     */
    public function actionIcon(): string
    {
        return match ($this->action) {
            'submitted'               => 'bi-send text-primary',
            'resubmitted'             => 'bi-arrow-repeat text-info',
            'approved_supervisor'     => 'bi-check-circle text-success',
            'approved_manager'        => 'bi-check-circle-fill text-success',
            'rejected'                => 'bi-x-circle-fill text-danger',
            'edited'                  => 'bi-pencil text-warning',
            'revision_requested'      => 'bi-arrow-repeat text-warning',
            'cancelled'               => 'bi-slash-circle text-danger',
            'odoo_posted'             => 'bi-cloud-check text-info',
            'acknowledged_controller' => 'bi-eye-fill text-teal',
            default                   => 'bi-info-circle text-secondary',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'submitted'               => 'Submitted',
            'resubmitted'             => 'Resubmitted as Revision',
            'approved_supervisor'     => 'Approved by Supervisor',
            'approved_manager'        => 'Approved by Manager',
            'rejected'                => 'Rejected',
            'edited'                  => 'Edited',
            'revision_requested'      => 'Revision Requested by Manager',
            'cancelled'               => 'Cancelled',
            'odoo_posted'             => 'Posted to Odoo',
            'acknowledged_controller' => 'Acknowledged by Controller',
            default                   => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
