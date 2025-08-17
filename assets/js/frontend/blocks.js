/**
 * External dependencies
 */
const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { decodeEntities } = window.wp.htmlEntities;
const { createElement } = window.React;

/**
 * Internal dependencies
 */
const settings = getSetting( 'paymento_gateway_data', {} );

/**
 * Label component
 */
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components;
    return createElement( PaymentMethodLabel, { text: decodeEntities( settings.title ) } );
};

/**
 * Content component - what shows in the payment method
 */
const Content = () => {
    return createElement(
        'div',
        {
            style: {
                padding: '12px 0',
            }
        },
        [
            // Payment method description
            settings.description && createElement(
                'p',
                {
                    key: 'description',
                    style: {
                        margin: '0 0 12px 0',
                        fontSize: '14px',
                        color: '#666',
                    }
                },
                decodeEntities( settings.description )
            ),
            
            // Crypto payment info
            createElement(
                'div',
                {
                    key: 'crypto-info',
                    style: {
                        background: '#f8f9fa',
                        padding: '12px',
                        borderRadius: '4px',
                        border: '1px solid #e9ecef',
                    }
                },
                [
                    createElement(
                        'p',
                        {
                            key: 'info-text',
                            style: {
                                margin: '0 0 8px 0',
                                fontSize: '13px',
                                fontWeight: '500',
                            }
                        },
                        '🔒 Secure Cryptocurrency Payment'
                    ),
                    createElement(
                        'p',
                        {
                            key: 'supported-coins',
                            style: {
                                margin: '0',
                                fontSize: '12px',
                                color: '#666',
                            }
                        },
                        'Supports Bitcoin, Ethereum, USDT and more'
                    )
                ]
            )
        ]
    );
};

/**
 * Paymento payment method config object
 */
const PaymentoPaymentMethod = {
    name: 'paymento_gateway',
    label: createElement( Label ),
    content: createElement( Content ),
    edit: createElement( Content ),
    canMakePayment: () => true,
    ariaLabel: decodeEntities( settings.title ),
    supports: {
        features: settings.supports || [],
    },
};

// Register the payment method
registerPaymentMethod( PaymentoPaymentMethod );
