<?php

namespace AppMaker\Actions;

class DeleteBulkAction extends BulkAction
{
    public function __construct()
    {
        parent::__construct('delete');

        $this->label('Delete Selected')
            ->icon('trash')
            ->color('red')
            ->requiresConfirmation(
                true,
                'Delete Records?',
                'Are you sure you want to delete the selected records? This action cannot be undone.'
            )
            ->action(function ($records) {
                $records->each->delete();
            });
    }
}
