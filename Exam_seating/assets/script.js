document.addEventListener('DOMContentLoaded', () => {
    
    const seatingGrid = document.getElementById('seating-grid');
    const seatDetails = document.getElementById('seat-details');

    // Use the global variables set in room_view.php
    fetch(`php/get_seating_plan.php?exam_id=${EXAM_ID}&room_id=${ROOM_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                seatingGrid.innerHTML = `<p class="error-message">${data.error}</p>`;
                return;
            }

            const { roomDetails, seatingPlan } = data;
            
            // Clear loading message
            seatingGrid.innerHTML = '';
            
            const planMap = {};
            seatingPlan.forEach(student => {
                planMap[`${student.seat_row}-${student.seat_col}`] = student;
            });
            
            seatingGrid.style.gridTemplateColumns = `repeat(${roomDetails.cols}, 1fr)`;
            
            for (let r = 1; r <= roomDetails.rows; r++) {
                for (let c = 1; c <= roomDetails.cols; c++) {
                    const seat = document.createElement('div');
                    seat.classList.add('seat');
                    
                    const key = `${r}-${c}`;
                    const student = planMap[key];
                    
                    if (student) {
                        seat.classList.add('occupied');
                        seat.innerHTML = `<strong>${student.roll_no}</strong><br><small>${student.name.split(' ')[0]}</small>`;
                        seat.dataset.studentName = student.name;
                        seat.dataset.rollNo = student.roll_no;
                        seat.dataset.classDept = student.class_dept;
                    } else {
                        seat.classList.add('empty');
                        seat.textContent = `${r}-${c}`;
                    }
                    
                    seat.dataset.row = r;
                    seat.dataset.col = c;

                    seat.addEventListener('click', () => displaySeatDetails(seat));
                    seatingGrid.appendChild(seat);
                }
            }
        })
        .catch(error => {
            console.error('Error fetching seating plan:', error);
            seatingGrid.innerHTML = '<p class="error-message">Failed to load seating plan.</p>';
        });

    function displaySeatDetails(seatElement) {
        const row = seatElement.dataset.row;
        const col = seatElement.dataset.col;
        
        if (seatElement.classList.contains('occupied')) {
            seatDetails.innerHTML = `
                <h4>Seat Information</h4>
                <p><strong>Seat:</strong> Row ${row}, Col ${col}</p>
                <p><strong>Student Name:</strong> ${seatElement.dataset.studentName}</p>
                <p><strong>Roll No:</strong> ${seatElement.dataset.rollNo}</p>
                <p><strong>Class:</strong> ${seatElement.dataset.classDept}</p>
            `;
        } else {
            seatDetails.innerHTML = `
                <h4>Seat Information</h4>
                <p><strong>Seat:</strong> Row ${row}, Col ${col}</p>
                <p><strong>Status:</strong> Empty</p>
            `;
        }
    }
});
