<?php namespace Cms\Models;

use App;
use Model;
use Exception;

/**
 * ThemeLog logs changes made to the theme
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class SmsLog extends Model
{
    /**
     * @var string table associated with the model
     */
    protected $table = 'log_sms_inbound';

}
