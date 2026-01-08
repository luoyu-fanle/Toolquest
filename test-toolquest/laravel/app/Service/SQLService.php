<?php

namespace App\Service;
use App\Models\QuoteModel;
use Illuminate\Support\Facades\DB;

class SQLService
{

    public function vulnerableQuery($id)
    {
        // We gebruiken DB::select omdat dit een array van objecten teruggeeft
        return DB::select("SELECT * FROM logins WHERE id = " . $id);
    }

    // De veilige motor: gebruikt placeholders
    public function safeQuery($id)
    {
        return DB::select("SELECT * FROM logins WHERE id = ?", [$id]);
    }

}