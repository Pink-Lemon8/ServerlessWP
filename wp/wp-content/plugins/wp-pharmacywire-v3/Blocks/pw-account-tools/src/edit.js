/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
import metadata from '../block.json';
/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */

import {
	ToggleControl,
	Panel,
	PanelBody,
} from '@wordpress/components';

function AccountToolsOptions(props) {
	function pwireToggles(props) {
		let togglesObj = {
			"verticalMenu" : {
				"type": "object",
				"default": { 
					"id": "verticalMenu",
					"class": "toggle-vertical-menu",
					"label": "Vertical menu instead of horizontal.",
					"value": false
				}
			},
			"hideHeading" : {
				"type": "object",
				"default": { 
					"id": "hideHeading",
					"class": "toggle-hide-heading",
					"label": "Hide Account Heading.",
					"value": false
				}
			},
			"hideAccount" : {
				"type": "object",
				"default": { 
					"id": "hideAccount",
					"class": "toggle-hide-account",
					"label": "Hide Account link.",
					"value": false
				}
			},
			"hideLoginLogoutLink" : {
				"type": "object",
				"default": { 
					"id": "hideLoginLogoutLink",
					"class": "toggle-login-logout-link",
					"label": "Hide Login/Log Out Link.",
					"value": false
				}
			},
			"hideCartLink" : {
				"type": "object",
				"default": { 
					"id": "hideCartLink",
					"class": "toggle-cart-link",
					"label": "Hide Cart Link.",
					"value": false
				}
			}
		};

		function onChange( toggleVal, toggleKey, props ) {
			props.setAttributes( { [toggleKey]: toggleVal } );
		}

		let togglesArray = [];
		for (const [toggleKey, toggleC] of Object.entries(togglesObj)) {
			const currentAtts = props.attributes[toggleKey];
			let tLabel = toggleC.label ? toggleC.label : toggleC.default.label;
			let tValue = (typeof currentAtts === 'boolean') ? currentAtts : toggleC.default.value;
			togglesArray.push(<ToggleControl
				key={toggleKey}
				className={`pw-ui-toggle ${toggleC.default.class}`}
				label={tLabel}
				// help={
				// 	tValue
				// 		? 'Has fixed background.'
				// 		: 'No fixed background.'
				// }
				checked={ tValue }
				onChange={ (toggleVal) => { onChange(toggleVal, toggleKey, props); } }
			/>);
		}
		return togglesArray;
	}

	return (
		<Panel>
			<PanelBody
				title={__(metadata.title, '')}
				icon={'admin-users'}
			>
				{pwireToggles(props)}
			</PanelBody>
		</Panel>
	);
}

export default function edit(props) {
	return (
		<div {...useBlockProps()} >
			<AccountToolsOptions { ...props } />
		</div>
	);
}
