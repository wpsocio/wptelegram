<?php
/**
 * Environment.
 *
 * @package WPSocio\TelegramFormatText
 */

namespace WPSocio\TelegramFormatText;

use WPSocio\TelegramFormatText\Converter\BlockquoteConverter;
use WPSocio\TelegramFormatText\Converter\CodeConverter;
use WPSocio\TelegramFormatText\Converter\CommentConverter;
use WPSocio\TelegramFormatText\Converter\ConverterInterface;
use WPSocio\TelegramFormatText\Converter\DefaultConverter;
use WPSocio\TelegramFormatText\Converter\EmphasisConverter;
use WPSocio\TelegramFormatText\Converter\HorizontalRuleConverter;
use WPSocio\TelegramFormatText\Converter\ImageConverter;
use WPSocio\TelegramFormatText\Converter\LinkConverter;
use WPSocio\TelegramFormatText\Converter\ListBlockConverter;
use WPSocio\TelegramFormatText\Converter\ListItemConverter;
use WPSocio\TelegramFormatText\Converter\PreformattedConverter;
use WPSocio\TelegramFormatText\Converter\SpoilerConverter;
use WPSocio\TelegramFormatText\Converter\TableConverter;
use WPSocio\TelegramFormatText\Converter\TextConverter;

/**
 * Class Environment
 */
final class Environment {

	/**
	 * Configuration.
	 *
	 * @var Configuration
	 */
	protected $config;

	/**
	 * Converters.
	 *
	 * @var ConverterInterface[]
	 */
	protected $converters = [];

	/**
	 * Environment constructor.
	 *
	 * @param array<string, mixed> $config Configuration.
	 */
	public function __construct( array $config = [] ) {
		$this->config = new Configuration( $config );
		$this->addConverter( new DefaultConverter() );
	}

	/**
	 * Get configuration.
	 *
	 * @return Configuration
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Add converter.
	 *
	 * @param ConverterInterface $converter Converter.
	 *
	 * @return void
	 */
	public function addConverter( ConverterInterface $converter ) {
		$converter->setConfig( $this->config );

		foreach ( $converter->getSupportedTags() as $tag ) {
			$this->converters[ $tag ] = $converter;
		}
	}

	/**
	 * Get converter by tag.
	 *
	 * @param string $tag Tag.
	 *
	 * @return ConverterInterface
	 */
	public function getConverterByTag( string $tag ) {
		if ( isset( $this->converters[ $tag ] ) ) {
			return $this->converters[ $tag ];
		}

		return $this->converters[ DefaultConverter::DEFAULT_CONVERTER ];
	}

	/**
	 * Create default environment.
	 *
	 * @param array<string, mixed> $config Configuration.
	 *
	 * @return Environment
	 */
	public static function createDefaultEnvironment( array $config = [] ) {
		$environment = new self( $config );

		$environment->addConverter( new CodeConverter() );
		$environment->addConverter( new CommentConverter() );
		$environment->addConverter( new EmphasisConverter() );
		$environment->addConverter( new HorizontalRuleConverter() );
		$environment->addConverter( new LinkConverter() );
		$environment->addConverter( new ImageConverter() );
		$environment->addConverter( new ListBlockConverter() );
		$environment->addConverter( new ListItemConverter() );
		$environment->addConverter( new PreformattedConverter() );
		$environment->addConverter( new SpoilerConverter() );
		$environment->addConverter( new TableConverter() );
		$environment->addConverter( new TextConverter() );
		$environment->addConverter( new BlockquoteConverter() );

		return $environment;
	}
}
