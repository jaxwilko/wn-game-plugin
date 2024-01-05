<?php namespace JaxWilko\Game\Models;

use Model;
use System\Classes\MediaLibrary;

/**
 * Item Model
 */
class Item extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_game_items';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'code',
        'data'
    ];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [
        'data'
    ];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getDataArray(): array
    {
        $data = $this->data;

        $data['size'] = array_map(fn($i) => (int) $i, explode(',', $data['size']));

        $data['icon'] = MediaLibrary::url($data['icon']);

        $spriteMap = $data['spriteMap'];
        $data['spriteMap'] = [];

        foreach ($spriteMap as $item) {
            $item['align'] = array_map(fn($i) => (int) $i, explode(',', $item['align']));
            $item['sheet'] = MediaLibrary::url($item['sheet']);
            $data['spriteMap'][$item['state']] = array_except($item, 'state');
        }

        return [
            $this->code => $data
        ];
    }
}
