/**
 * UpSolution Element: Interactive Text
 */
!function( $ ) {
	"use strict";
	/**
	 * @class WItext
	 * @param {string|node} container
	 * @return void
	 */
	$us.WItext = function( container ) {
		// Elements
		this.$container = $( container );
		var $parts = this.$container.find( '.w-itext-part' );

		if ( $parts.length === 0 ) {
			return; // No animated parts
		}

		// Get options
		var options = this.$container[ 0 ].onclick() || {};
		this.$container.removeAttr( 'onclick' );

		// Set options
		var type = this.$container.usMod( 'type' );
		this.animateChars = ( type.substring( type.length - 'chars'.length ).toLowerCase() === 'chars' );
		this.duration = parseInt( options.duration ) || 1000;
		this.delay = parseInt( options.delay ) || 5000;
		this.dynamicColor = ( options.dynamicColor || '' );
		this.animateDurations = []; // Time of all animations
		this.type = this.animateChars ? type.substring( 0, type.length - 'chars'.length ) : type;

		// Creating objects for all parts
		this.parts = [];
		$parts
			.css( { transitionDuration: this.duration + 'ms' } )
			.each( function( index, part ) {
				var part = {
					$node: $( part ),
					currentState: 0,
					states: part.onclick() || []
				};
				part.$node.removeAttr( 'onclick' );
				if ( this.dynamicColor ) {
					part.$node.css( 'color', this.dynamicColor );
				}
				this.parts[ index ] = part;
			}.bind( this ) );

		// Start first animation
		var timer = $us.timeout( function() {
			this.parts.map( function( part ) {
				this._events.startAnimate.call( this, part );
			}.bind( this ) );
			$us.clearTimeout( timer );
		}.bind( this ), this.delay );

	};
	// Export api
	$us.WItext.prototype = {
		_events: {
			/**
			 * Starts the next animation step.
			 * @param {object} part
			 * @return void
			 */
			startAnimate: function( part ) {
				part.currentState = ( part.currentState === part.states.length - 1 ) ? 0 : ( part.currentState + 1 );
				this.render.call( this, part );
			},
			/**
			 * Animation restart
			 * @param {object} part
			 * @return void
			 */
			restartAnimate: function( part ) {
				$us.timeout( this._events.startAnimate.bind( this, part ), this.delay );
			},
			/**
			 * Clear unwanted items or data after animation
			 * @param {object} part
			 * @return void
			 */
			clearAnimation: function( part ) {
				part.$node
					.html( part.states[ part.currentState ].replace( ' ', '&nbsp;' ) )
					.css( 'width', '' );
				if ( this.type === 'typing' ) {
					part.$node.append( '<i class="w-itext-cursor"></i>' );
				}
				if ( part.curDuration === Math.max.apply( null, this.animateDurations ) ) {
					this.animateDurations = [];
					this.parts.map( function( _part ) {
						this._events.restartAnimate.call( this, _part );
					}.bind( this ) );
				}
			}
		},
		/**
		 * Rendering animation elements
		 * @param {object} part
		 * @return void
		 */
		render: function( part ) {
			var nextValue = part.states[ part.currentState ],
				$curSpan = part.$node.wrapInner( '<span></span>' ).children( 'span' ),
				$nextSpan = $( '<span class="measure"></span>' ).html( nextValue.replace( ' ', '&nbsp;' ) ).appendTo( part.$node ),
				nextWidth = $nextSpan.width(),
				outType = 'fadeOut',
				startDelay = 0;
			part.curDuration = this.duration;
			// Remove typing chars
			if ( this.type === 'typing' ) {
				var oldValue = $.trim( $curSpan.text() ) + ' ',
					removeDuration = Math.floor( part.curDuration / 3 );
				startDelay = Math.max.apply( null, [ startDelay, ( removeDuration * oldValue.length ) ] );
				for ( var i = 0; i < oldValue.length; i ++ ) {
					$curSpan.text( oldValue );
					$us.timeout( function() {
						var text = $curSpan.text();
						$curSpan.text( text.substring( 0, text.length - 1 ) );
					}.bind( this ), removeDuration * i );
				}
			}
			// Start animation
			$us.timeout( function() {
				part.$node.addClass( 'notransition' ).css( 'width', part.$node.width() );
				$us.timeout( function() {
					part.$node.removeClass( 'notransition' ).css( 'width', nextWidth );
				}.bind( this ), 25 );
				if ( this.type !== 'typing' ) {
					$curSpan
						.css({
							position: 'absolute',
							top: 0,
							left: 0,
							width: nextWidth,
							transitionDuration: ( this.duration / 5 ) + 'ms'
						})
						.addClass( 'animated_' + outType );
				}
				$nextSpan.removeClass( 'measure' ).css( 'width', nextWidth ).prependTo( part.$node )
				if ( this.animateChars ) {
					$nextSpan.empty();
					if ( this.type === 'typing' ) {
						$nextSpan.append( '<span class="w-itext-part-nospan"></span>' );
					}
					for ( var i = 0; i < nextValue.length; i ++ ) {
						var $char = ( ( nextValue[ i ] !== ' ' ) ? nextValue[ i ] : '&nbsp;' );
						if ( this.type !== 'typing' ) {
							$char = $( '<span>' + $char + '</span>' );
							$char
								.css( 'transition-duration', part.curDuration + 'ms' )
								.appendTo( $nextSpan );
							$char.appendTo( $nextSpan );
						}
						$us.timeout( function( $char ) {
							if ( this.type !== 'typing' ) {
								$char.addClass( 'animated_' + this.type );
							} else {
								var $text = $( '> span:first', $nextSpan );
								$text.html( $text.html() + $char );
							}
						}.bind( this, $char ), part.curDuration * i );
					}

					if ( this.type === 'typing' ) {
						$nextSpan.append( '<i class="w-itext-cursor"></i>' );
					}
					part.curDuration *= ( nextValue.length + 1 );
				} else {
					$nextSpan.wrapInner( '<span></span>' ).children( 'span' ).css( {
						'transition-duration': this.duration + 'ms'
					} ).addClass( 'animated_' + this.type );
				}
				this.animateDurations.push( part.curDuration );

				// Clear and next animation state
				$us.timeout( this._events.clearAnimation.bind( this, part ), part.curDuration + Math.floor( this.delay / 3 ) );
			}.bind( this ), startDelay );
		}
	};

	$.fn.wItext = function( options ) {
		return this.each( function() {
			$( this ).data( 'wItext', new $us.WItext( this, options ) );
		} );
	};

	$( function() {
		$( '.w-itext' ).wItext();
	} );

}( jQuery );
