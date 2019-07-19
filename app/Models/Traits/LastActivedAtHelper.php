<?php
namespace App\Models\Traits;
use Redis;
use Carbon\Carbon;

trait LastActivedAtHelper
{
	protected $hash_prefix = 'larabbs_last_actived_at_';
	protected $field_prefix = 'user_';

	public function recordLastActivedAt()
	{
		$date = Carbon::now()->toDateString();
		$hash = $this->getHashFromDateString($date);
		// dd(Redis::hGetALl($hash));
		$field = $this->getHashField();
		$now = Carbon::now()->toDateTimeString();
		Redis::hset($hash,$field,$now);
	}

	public function syncUserActivedAt()
	{
		// $yesterday_date = Carbon::yesterday()->toDateString();
		$yesterday_date = Carbon::now()->toDateString();
		$hash = $this->getHashFromDateString($date);
		$dates = Redis::hGetAll($hash);
		foreach ($dates as $user_id => $actived_at) {
			$user_id = str_replace($this->field_prefix, '', $user_id);
			if($user = $this->find($user_id))
			{
				$user->last_actived_at = $actived_at;
				$user->save();
			}
		}
		Redis::del($hash);
	}

	public function getLastActivedAtAttribute($value)
	{
		$date = Carbon::now()->toDateString();
		$hash = $this->getHashFromDateString($date);
		$field = $this->getHashField();
		$datetime = Redis::hGet($hash, $field) ?: $value;
		if($datetime)
		{
			return new Carbon($datetime);
		}
		else
		{
			return $this->created_at;
		}
	}

	public function getHashFromDateString($date)
	{
		return $this->hash_prefix.$date;
	}

	public function getHashField()
	{
		return $this->field_prefix.$this->id;
	}
}