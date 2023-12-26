<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action('after_setup_theme', 'load_carbon_fields');

add_action('carbon_fields_register_fields','create_options_page');

function load_carbon_fields()
{
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page()
{
    Container::make( 'theme_options', __( 'Candidates Proposal' ) )
    ->set_icon('dashicons-media-text')
    ->add_fields( array(

        Field::make( 'checkbox', 'candidates_proposal_plugin_active', __( 'Active' )),

        Field::make( 'text', 'candidates_proposal_plugin_days', __( 'Time between votes in days' ) )
        ->set_attribute( 'min', 0 )
        ->set_attribute( 'max', 365 )
        ->set_attribute( 'maxLength', 3 )
        ->set_default_value( 1 )
        ->set_help_text('Number of days between two vote casts by the same user to the same candidate'),

        Field::make( 'textarea', 'candidates_proposal_plugin_message', __( 'Confirmation Message' ))
        ->set_attribute('placeholder','Enter the confirmation message')
        ->set_help_text('Type the message you want the submitter to receive')
    ) );

}