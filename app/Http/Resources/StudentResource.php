<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolve profile image URL
        $profileImgUrl = asset('images/default-avatar.png');
        if ($this->profile_img && $this->profile_img !== 'default.png') {
            $profileImgUrl = asset('storage/profile_images/' . $this->profile_img);
        }

        // Map Gender
        $genderMap = [
            0 => 'Other',
            1 => 'Male',
            2 => 'Female',
        ];
        $gender = $genderMap[$this->gender] ?? 'Other';

        return [
            'student_id' => $this->student_id,
            'name' => $this->firstname . ' ' . $this->lastname, // Combined name often used
            'gender' => $gender,
            'address' => $this->address,
            'pincode' => $this->pincode,
            'std' => $this->std,
            'email' => $this->email,
            'dob' => $this->dob ? Carbon::parse($this->dob)->format('Y-m-d') : null,
            'doj' => $this->doj ? Carbon::parse($this->doj)->format('Y-m-d') : null,
            'dadno' => $this->dadno,
            'dadwp' => $this->dadwp,
            'selfno' => $this->selfno,
            'selfwp' => $this->selfwp,
            'momno' => $this->momno,
            'momwp' => $this->momwp,
            'branch' => $this->branch ? $this->branch->name : null,
            'branch_id' => $this->branch_id,
            'belt' => $this->belt ? $this->belt->name : null,
            'belt_id' => $this->belt_id,
            'profile_img_url' => $profileImgUrl,
            'active' => (bool) $this->active,

            // Include fastrack info if loaded or available (conditional attributes)
            'fastrack_attendance' => $this->when(isset($this->resource->fastrack_attendance), function () {
                return $this->resource->fastrack_attendance;
            }),
            'my_fastrack_attendance' => $this->when(isset($this->resource->my_fastrack_attendance), function () {
                return $this->resource->my_fastrack_attendance;
            }),
            'total_fastrack_records' => $this->when(isset($this->resource->total_fastrack_records), function () {
                return $this->resource->total_fastrack_records;
            }),

            // Original structure compatibility
            'gr_no' => $this->gr_no ?? "STU-" . ($this->doj ? Carbon::parse($this->doj)->format('Y') : date('Y')) . "-{$this->student_id}",
        ];
    }
}
