<?php namespace JaxWilko\Game\Models;

use Model;

/**
 * Quest Model
 */
class Quest extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_game_quests';

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

        if (empty($data['completion'])) {
            unset($data['completion']);
        }

        $prerequisites = $data['prerequisite'];
        $data['prerequisite'] = [];
        foreach ($prerequisites as $prerequisite) {
            $data['prerequisite'][] = $prerequisite['quest'];
        }

        $rewards = $data['reward'];
        $data['reward'] = [];
        foreach ($rewards as $reward) {
            $data['reward'][$reward['code']] = $reward['quantity'];
        }

        $data['repeatable'] = (bool) $data['repeatable'];

        return [
            $this->code => $data
        ];
    }
}
