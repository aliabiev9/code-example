<?php

namespace App\Models;

use App\Http\Requests\Contact\ContactRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contact
 * @package App\Models
 *
 * @property string $title
 * @property string $address
 * @property string $email
 * @property string $phone
 * @property string $phone_whatsupp
 * @property string $phone_viber
 * @property string $web_site_url
 */
class Contact extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'title', 'address', 'email', 'phone', 'phone_whatsupp', 'phone_viber', 'web_site_url'
    ];

    /**
     * @param ContactRequest $request
     * @return bool
     */
    public function updateContact(ContactRequest $request)
    {
        return $this->update($request->all());
    }

}
