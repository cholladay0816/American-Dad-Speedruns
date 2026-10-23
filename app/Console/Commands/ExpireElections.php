<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
class ExpireElections extends Command
{
    protected $signature = 'election:expire';
    protected $description = 'Retired command; historical elections are preserved.';
    public function handle() { return 0; }
}
