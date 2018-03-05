<?php

namespace App\Framework\Router;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
	public const DEFAULT_CONTROLLER_PATH = 'App\\Controller\\';

	/** @var string */
	private $method;

	/** @var string */
	private $path;

	/** @var string|callable */
	private $callback;

	/** @var string */
	private $name;

	/** @var string */
	private $regex;

	/** @var array */
	private $params = [];

	/**
	 * Route constructor.
	 *
	 * @param string $method
	 * @param string $path
	 * @param string $regex
	 * @param string $name
	 * @param string|callable $callback
	 */
	public function __construct(string $method, string $path, string $regex, string $name, $callback)
	{
		$this->method = $method;
		$this->regex = $regex;
		$this->path = $path;
		$this->name = $name;
		$this->callback = $callback;
	}

	/**
	 * Return true or false
	 *
	 * @param ServerRequestInterface $request
	 * @return bool
	 */
	public function match(ServerRequestInterface $request): bool
	{
		if ($request->getMethod() === $this->method) {
			if (preg_match($this->regex, trim($request->getUri()->getPath(), '/'), $params)) {
				array_shift($params);

				if ($request->getMethod() === 'POST') {
					array_unshift($params, $request);
				}

				$this->params = $params;
				return true;
			}
		}

		return false;
	}

	/**
	 * Execute the callback
	 *
	 * @param Router $router
	 * @return mixed
	 */
	public function call(Router $router)
	{
		$callable = $this->callback;

		if (is_string($this->callback)) {
			$actions = explode('@', $this->callback);
			$className = self::DEFAULT_CONTROLLER_PATH . $actions[0];
			$methodName = $actions[1];
			$callable = [new $className($router), $methodName];
		}

		return call_user_func_array($callable, $this->params);
	}

	/**
	 * Get uri from current route
	 *
	 * @param array $params
	 * @return string
	 */
	public function getUri(array $params): string
	{
		$path = $this->path;
		foreach ($params as $key => $value) {
			$path = str_replace('{' . $key . '}', $value, $path);
		}

		return $path;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * @param string $method
	 */
	public function setMethod(string $method): void
	{
		$this->method = $method;
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath(string $path): void
	{
		$this->path = $path;
	}

	/**
	 * @return callable|string
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * @param callable|string $callback
	 */
	public function setCallback($callback): void
	{
		$this->callback = $callback;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getRegex(): string
	{
		return $this->regex;
	}

	/**
	 * @param string $regex
	 */
	public function setRegex(string $regex): void
	{
		$this->regex = $regex;
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @param array $params
	 */
	public function setParams(array $params): void
	{
		$this->params = $params;
	}
}