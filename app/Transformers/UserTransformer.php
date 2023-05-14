<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;

class UserTransformer extends TransformerAbstract {

    public function transform(User $user)
    {
        return [
                'user'=> [
                    'firstname' => $user->first_name,
                    'lastname' => $user->last_name,
                    'email' => $user->email,
                    'userid' => $user->id,
                    'field_manager' => $user->field_manager_phone,
                ],
            ];
    }
}
?>