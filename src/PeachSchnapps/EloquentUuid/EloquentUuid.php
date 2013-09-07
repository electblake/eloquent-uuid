<?php namespace PeachSchnapps\EloquentUuid;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;
// use Raveren\Kint\Kint;
class EloquentUuid {

	/**
	 * The configuration array
	 *
	 * @var array
	 */
	protected $config = array(
		'method' => 'uuid1',
		'opts' => array()
	);

	/**
	 * Default options for various uuid methods
	 * @url https://github.com/ramsey/uuid
	 * @var array
	 */
	protected $method_opts_map = array(
		'uuid1' => null,
		'uuid4' => null
		// others defined in __construct()
	);

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct( array $config ) {

		$this->config = $config;
		/**
		 * For full details checkout https://github.com/ramsey/uuid
		 */
		$this->method_opts_map['uuid3'] = array(Uuid::NAMESPACE_DNS, $this->config['hash_key']); // md5
		$this->method_opts_map['uuid5'] = array(Uuid::NAMESPACE_DNS, $this->config['hash_key']); // sha1

	}

	/**
	 * Auto generate uuid for the model
	 *
	 * @param  Model     $model The model
	 * @param  boolean   $force Force generation of a uuid
	 * @return boolean
	 */
	public function make( Model $model, $force = false)
	{

		// if the model isn't uuid, then do nothing
		if ( !isset( $model::$uuid ) ) {
			return true;
		}

		// make sure we have auto-incrementing id turned off
		if ($model->incrementing) {
			$model->incrementing = false;
		}

		// load the configuration
		$config = array_merge( $this->config, $model::$uuid );

		// nicer variables for readability
		$method = $opts = null;
		extract( $config, EXTR_IF_EXISTS );

		if (empty($opts)) {
			$opts = $this->method_opts_map[$method];
		}

		if (!$model->id) {
			if (is_callable($method)) {
				$uuid = call_user_func_array($method, $opts);
			} else if ( method_exists('Rhumsaa\Uuid\Uuid', $method)) {
				
				$uuid = Uuid::$method($opts);

			} else if ( $method instanceof Closure ) {

				$uuid = call_user_func_array($method, $opts);

			} else if ( is_callable( $method ) ) {

				$uuid = call_user_func_array( $method, $opts );

			} else {

				throw new \UnexpectedValueException("Uuid method is not a callable, closure or null.");

			}

			if (empty($uuid)) {
				throw new \UnexpectedValueException("Uuid failed to generate.");
			}

			$model->id = $uuid;
		}

		return true;

		// skip slug generation if the model exists or the slug field is already populated,
		// and on_update is false ... unless we are forcing things!

		// if (!$force) {
		// 	if ( ( $model->exists || !empty($model->{$save_to}) ) && !$on_update ) {
		// 		return true;
		// 	}
		// }


		// build the slug string

		// if ( is_string($build_from) ) {

		// 	$string = $model->{$build_from};

		// } else if ( is_array( $build_from ) ) {

		// 	$string = '';
		// 	foreach( $build_from as $field ) {
		// 		$string .= $model->{$field} . ' ';
		// 	}

		// } else {

		// 	$string = $model->__toString();
		// }

		// $string = trim( $string );


		// build slug using given slug style




		// // check for uniqueness?

		// if ( $unique ) {

		// 	// find all models where the slug is similar to the generated slug

		// 	$class = get_class($model);

		// 	$collection = $class::where( $save_to, 'LIKE', $slug.'%' )
		// 		->orderBy( $save_to, 'DESC' )
		// 		->get();


		// 	// extract the slug fields

		// 	$list = $collection->lists( $save_to, $model->getKeyName() );

		// 	// if the current model exists in the list -- i.e. the existing slug is either
		// 	// equal to or an incremented version of the new slug -- then the slug doesn't
		// 	// need to change and we can just return

		// 	if ( array_key_exists($model->getKey(), $list) ) {
		// 		return true;
		// 	}

		// 	// does the exact new slug exist?

		// 	if ( in_array($slug, $list) ) {

		// 		// find the "highest" numbered version of the slug and increment it.

		// 		$idx = substr( $collection->first()->{$save_to} , strlen($slug) );
		// 		$idx = ltrim( $idx, $separator );
		// 		$idx = intval( $idx );
		// 		$idx++;

		// 		$slug .= $separator . $idx;

		// 	}

		// }


		// update the slug field

		// $model->{$save_to} = $slug;


		// done!

	}


}