<?php

namespace App\Jobs;

use App\Mail\LoanEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoanEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $loan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $loan)
    {
        $this->user = $user;
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new LoanEmail($this->user, $this->loan));
    }
}
