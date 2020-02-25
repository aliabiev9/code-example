<?php

namespace App\Models;

use App\Http\Requests\AboutUs\AboutUsRequest;
use App\Models\Traits\Videoable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class AboutUs
 * @package App\Models
 * @property string $name
 * @property string $description
 * @property-read $video
 */
class AboutUs extends Model
{
    use Videoable;

    protected $fillable = ['name', 'description'];

    /**
     * @param AboutUsRequest $request
     * @return bool
     */
    public function updateAboutUs(AboutUsRequest $request)
    {
        return $this->update($request->all());
    }

    public function saveVideoIfExist(Request $request, AboutUs $about)
    {
        if ($request->video && $request->video !== 'undefined') {
            if ($about->video) {
                $about->video->delete();
            }
            $about->updateVideo($request->video);
        }
    }
}
